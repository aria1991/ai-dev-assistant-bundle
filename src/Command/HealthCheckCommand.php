<?php

declare(strict_types=1);

/*
 * This file is part of the AI Development Assistant Bundle.
 *
 * (c) Aria Vahidi <aria.vahidi2020@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aria1991\AIDevAssistantBundle\Command;

use Aria1991\AIDevAssistantBundle\Service\ConfigurationHelper;
use Aria1991\AIDevAssistantBundle\Service\Provider\AIProviderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Health check command for the AI Development Assistant Bundle.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
#[AsCommand(
    name: 'ai-dev-assistant:health',
    description: 'Check bundle health and configuration'
)]
final class HealthCheckCommand extends Command
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly iterable $providers,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('AI Development Assistant Bundle - Health Check');

        // Check configuration
        $configHealth = $this->checkConfiguration($io);

        // Check providers
        $providersHealth = $this->checkProviders($io);

        // Check system requirements
        $systemHealth = $this->checkSystemRequirements($io);

        $overallHealth = $configHealth && $providersHealth && $systemHealth;

        // Final status
        if ($overallHealth) {
            $io->success('All health checks passed. Bundle is ready for use.');

            return Command::SUCCESS;
        }

        $io->error('Some health checks failed. Please review the issues above.');

        return Command::FAILURE;
    }

    private function checkConfiguration(SymfonyStyle $io): bool
    {
        $io->section('Configuration Check');

        $config = [
            'ai' => [
                'providers' => [
                    'openai' => [
                        'api_key' => $this->parameterBag->get('env(OPENAI_API_KEY)'),
                        'model' => $this->parameterBag->get('ai_dev_assistant.ai.providers.openai.model'),
                    ],
                    'anthropic' => [
                        'api_key' => $this->parameterBag->get('env(ANTHROPIC_API_KEY)'),
                        'model' => $this->parameterBag->get('ai_dev_assistant.ai.providers.anthropic.model'),
                    ],
                    'google' => [
                        'api_key' => $this->parameterBag->get('env(GOOGLE_AI_API_KEY)'),
                        'model' => $this->parameterBag->get('ai_dev_assistant.ai.providers.google.model'),
                    ],
                ],
            ],
            'cache' => [
                'enabled' => $this->parameterBag->get('ai_dev_assistant.cache.enabled'),
                'ttl' => $this->parameterBag->get('ai_dev_assistant.cache.ttl'),
            ],
        ];

        $validation = ConfigurationHelper::validateConfiguration($config);

        $table = new Table($io);
        $table->setHeaders(['Check', 'Status', 'Details']);

        $hasErrors = !empty($validation['errors']);
        $hasWarnings = !empty($validation['warnings']);

        if (!$hasErrors && !$hasWarnings) {
            $table->addRow(['Configuration', '✅ PASS', 'All configuration valid']);
        }

        foreach ($validation['errors'] as $error) {
            $table->addRow(['Configuration', '❌ FAIL', $error]);
        }

        foreach ($validation['warnings'] as $warning) {
            $table->addRow(['Configuration', '⚠️  WARN', $warning]);
        }

        $table->render();

        return !$hasErrors;
    }

    private function checkProviders(SymfonyStyle $io): bool
    {
        $io->section('AI Providers Check');

        $table = new Table($io);
        $table->setHeaders(['Provider', 'Status', 'Details']);

        $anyAvailable = false;

        foreach ($this->providers as $provider) {
            if (!$provider instanceof AIProviderInterface) {
                continue;
            }

            $providerName = $this->getProviderName($provider);

            try {
                if ($provider->isAvailable()) {
                    $table->addRow([$providerName, '✅ AVAILABLE', 'Ready for requests']);
                    $anyAvailable = true;
                } else {
                    $table->addRow([$providerName, '⚠️  UNAVAILABLE', 'No API key configured']);
                }
            } catch (\Exception $e) {
                $table->addRow([$providerName, '❌ ERROR', $e->getMessage()]);
            }
        }

        $table->render();

        if (!$anyAvailable) {
            $io->error('No AI providers are available. Please configure at least one API key.');
        }

        return $anyAvailable;
    }

    private function checkSystemRequirements(SymfonyStyle $io): bool
    {
        $io->section('System Requirements Check');

        $table = new Table($io);
        $table->setHeaders(['Requirement', 'Status', 'Details']);

        $allPassed = true;
        $checks = [];

        // PHP version
        $phpVersion = \PHP_VERSION;
        $minPhpVersion = '8.2.0';
        $phpOk = version_compare($phpVersion, $minPhpVersion, '>=');
        $table->addRow([
            'PHP Version',
            $phpOk ? '✅ PASS' : '❌ FAIL',
            "Current: {$phpVersion}, Required: >= {$minPhpVersion}",
        ]);
        $checks[] = $phpOk;

        // Required extensions
        $requiredExtensions = ['json', 'curl', 'mbstring'];
        foreach ($requiredExtensions as $extension) {
            $loaded = \extension_loaded($extension);
            $table->addRow([
                "Extension: {$extension}",
                $loaded ? '✅ PASS' : '❌ FAIL',
                $loaded ? 'Loaded' : 'Not loaded',
            ]);
            $checks[] = $loaded;
        }

        // Memory limit
        $memoryLimit = \ini_get('memory_limit');
        $memoryOk = $memoryLimit === '-1' || $this->parseMemoryLimit($memoryLimit) >= 256 * 1024 * 1024;
        $table->addRow([
            'Memory Limit',
            $memoryOk ? '✅ PASS' : '⚠️  WARN',
            "Current: {$memoryLimit}, Recommended: >= 256M",
        ]);

        $table->render();

        $allPassed = !\in_array(false, $checks, true);

        return $allPassed;
    }

    private function getProviderName(AIProviderInterface $provider): string
    {
        $className = $provider::class;

        return str_replace(['Provider', 'Aria1991\\AIDevAssistantBundle\\Service\\Provider\\'], '', $className);
    }

    private function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        $lastChar = strtolower($limit[\strlen($limit) - 1]);
        $number = (int) substr($limit, 0, -1);

        return match ($lastChar) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => (int) $limit,
        };
    }
}

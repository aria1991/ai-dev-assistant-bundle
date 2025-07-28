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

use Aria1991\AIDevAssistantBundle\Service\AIManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to test AI provider configuration.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
#[AsCommand(
    name: 'ai-dev-assistant:config-test',
    description: 'Test AI provider configuration and connectivity'
)]
final class ConfigTestCommand extends Command
{
    public function __construct(
        private readonly AIManager $aiManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('AI Development Assistant - Configuration Test');

        // Check available providers
        $availableProviders = $this->aiManager->getAvailableProviders();

        if (empty($availableProviders)) {
            $io->error('No AI providers are configured or available!');
            $io->note([
                'Please configure at least one AI provider by setting the appropriate environment variables:',
                '- OPENAI_API_KEY for OpenAI',
                '- ANTHROPIC_API_KEY for Anthropic Claude',
                '- GOOGLE_AI_API_KEY for Google AI',
            ]);

            return Command::FAILURE;
        }

        $io->success(\sprintf('Found %d available AI provider(s):', \count($availableProviders)));

        foreach ($availableProviders as $provider) {
            $io->text("✓ {$provider->getName()}");
        }

        $io->newLine();

        // Test connectivity with a simple request
        $io->section('Testing AI connectivity...');

        try {
            $testPrompt = 'Please respond with exactly "AI_TEST_SUCCESS" to confirm connectivity.';
            $response = $this->aiManager->request($testPrompt);

            if (str_contains(strtoupper($response), 'AI_TEST_SUCCESS')) {
                $io->success('AI connectivity test passed!');
                $io->text("Response: {$response}");

                return Command::SUCCESS;
            } else {
                $io->warning('AI responded but with unexpected content:');
                $io->text("Response: {$response}");

                return Command::SUCCESS; // Still consider it a success since we got a response
            }
        } catch (\Exception $e) {
            $io->error('AI connectivity test failed: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}

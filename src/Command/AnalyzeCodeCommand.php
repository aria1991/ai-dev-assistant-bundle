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

use Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

/**
 * Command to analyze code files.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
#[AsCommand(
    name: 'ai-dev-assistant:analyze',
    description: 'Analyze PHP code files for security, performance, quality, and documentation issues'
)]
final class AnalyzeCodeCommand extends Command
{
    public function __construct(
        private readonly CodeAnalyzer $codeAnalyzer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::REQUIRED, 'File or directory path to analyze')
            ->addOption('analyzers', 'a', InputOption::VALUE_OPTIONAL, 'Comma-separated list of analyzers to run (security,performance,quality,documentation)')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format (text|json)', 'text')
            ->addOption('exclude', 'x', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Paths to exclude from analysis', ['vendor/', 'var/cache/', 'node_modules/'])
            ->addOption('max-files', 'm', InputOption::VALUE_OPTIONAL, 'Maximum number of files to analyze', 100)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('path');
        $format = $input->getOption('format');
        $maxFiles = (int) $input->getOption('max-files');
        $excludePaths = $input->getOption('exclude');

        // Parse analyzers option
        $enabledAnalyzers = null;
        if ($analyzersOption = $input->getOption('analyzers')) {
            $enabledAnalyzers = array_map('trim', explode(',', $analyzersOption));
            
            // Validate analyzer names
            $availableAnalyzers = $this->codeAnalyzer->getAnalyzerNames();
            $invalidAnalyzers = array_diff($enabledAnalyzers, $availableAnalyzers);
            
            if (!empty($invalidAnalyzers)) {
                $io->error('Invalid analyzers: ' . implode(', ', $invalidAnalyzers));
                $io->note('Available analyzers: ' . implode(', ', $availableAnalyzers));

                return Command::FAILURE;
            }
        }

        try {
            // Get files to analyze
            $files = $this->getFilesToAnalyze($path, $excludePaths, $maxFiles);
            
            if (empty($files)) {
                $io->warning('No PHP files found to analyze.');

                return Command::SUCCESS;
            }

            $io->info(\sprintf('Analyzing %d file(s)...', \count($files)));

            $results = [];
            $totalIssues = 0;
            $criticalIssues = 0;

            foreach ($files as $file) {
                $io->text("Analyzing: {$file}");
                
                try {
                    $result = $this->codeAnalyzer->analyzeFile($file, $enabledAnalyzers);
                    $results[] = $result;
                    
                    $totalIssues += $result['summary']['total_issues'] ?? 0;
                    $criticalIssues += $result['summary']['critical_issues'] ?? 0;
                    
                } catch (\Exception $e) {
                    $io->error("Failed to analyze {$file}: " . $e->getMessage());
                }
            }

            // Output results
            if ($format === 'json') {
                $json = json_encode($results, \JSON_PRETTY_PRINT);
                $output->write($json ?: '{}');
            } else {
                $this->outputTextResults($io, $results);
            }

            // Summary
            $io->newLine();
            $io->success(\sprintf(
                'Analysis complete! Found %d total issues (%d critical) across %d files.',
                $totalIssues,
                $criticalIssues,
                \count($files)
            ));

            return $criticalIssues > 0 ? Command::FAILURE : Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Analysis failed: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * @return string[]
     */
    private function getFilesToAnalyze(string $path, array $excludePaths, int $maxFiles): array
    {
        if (is_file($path)) {
            return [$path];
        }

        if (!is_dir($path)) {
            throw new \InvalidArgumentException("Path not found: {$path}");
        }

        $finder = new Finder();
        $finder->files()
            ->in($path)
            ->name('*.php')
            ->ignoreDotFiles(true)
            ->ignoreVCS(true);

        // Add exclude patterns
        foreach ($excludePaths as $excludePath) {
            $finder->notPath($excludePath);
        }

        $files = [];
        foreach ($finder as $file) {
            $files[] = $file->getRealPath();
            
            if (\count($files) >= $maxFiles) {
                break;
            }
        }

        return $files;
    }

    private function outputTextResults(SymfonyStyle $io, array $results): void
    {
        foreach ($results as $result) {
            $filename = $result['filename'] ?? 'Unknown';
            $summary = $result['summary'] ?? [];
            $riskScore = $result['risk_score'] ?? 'unknown';

            $io->section("File: {$filename}");
            $io->text("Risk Score: {$riskScore}");
            $io->text(\sprintf(
                'Issues: %d total (%d critical, %d high, %d medium, %d low)',
                $summary['total_issues'] ?? 0,
                $summary['critical_issues'] ?? 0,
                $summary['high_issues'] ?? 0,
                $summary['medium_issues'] ?? 0,
                $summary['low_issues'] ?? 0
            ));

            // Show issues by analyzer
            foreach ($result['analyzers'] ?? [] as $analyzerName => $analyzerResult) {
                if (isset($analyzerResult['error'])) {
                    $io->warning("{$analyzerName}: {$analyzerResult['error']}");
                    continue;
                }

                $issues = $analyzerResult['issues'] ?? [];
                if (!empty($issues)) {
                    $io->text("\n{$analyzerName} issues:");
                    foreach ($issues as $issue) {
                        $severity = strtoupper($issue['severity'] ?? 'UNKNOWN');
                        $line = $issue['line'] ?? '?';
                        $message = $issue['message'] ?? 'No message';
                        
                        $io->text("  [{$severity}] Line {$line}: {$message}");
                    }
                }
            }

            $io->newLine();
        }
    }
}


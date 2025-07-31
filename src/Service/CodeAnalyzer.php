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

namespace Aria1991\AIDevAssistantBundle\Service;

use Aria1991\AIDevAssistantBundle\Exception\AIProviderException;
use Aria1991\AIDevAssistantBundle\Exception\AnalysisException;
use Aria1991\AIDevAssistantBundle\Exception\FileException;
use Aria1991\AIDevAssistantBundle\Exception\InvalidCodeException;
use Aria1991\AIDevAssistantBundle\Service\Analyzer\AnalyzerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;

/**
 * Main code analyzer service with robust error handling.
 *
 * This service coordinates different analysis types and provides
 * comprehensive error handling for all failure scenarios.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class CodeAnalyzer
{
    private const MAX_FILE_SIZE = 1024 * 1024; // 1MB
    private const SUPPORTED_EXTENSIONS = ['php'];
    private const DEFAULT_CACHE_TTL = 3600; // 1 hour

    public function __construct(
        private readonly AIManagerInterface $aiManager,
        private readonly CacheServiceInterface $cacheService,
        private readonly LoggerInterface $logger,
        private readonly AnalyzerInterface $qualityAnalyzer,
        private readonly AnalyzerInterface $securityAnalyzer,
        private readonly AnalyzerInterface $performanceAnalyzer,
        private readonly AnalyzerInterface $documentationAnalyzer,
    ) {
    }

    /**
     * Analyze code string with comprehensive error handling.
     *
     * @param string $code         The PHP code to analyze
     * @param string $filePath     Optional file path for context
     * @param string $analysisType Type of analysis: 'comprehensive', 'quality', 'security', 'performance', 'documentation'
     * @param array  $options      Additional options for analysis
     *
     * @throws AnalysisException    When analysis fails
     * @throws InvalidCodeException When code is invalid
     *
     * @return array Analysis results with success/error status
     */
    public function analyzeCode(
        string $code,
        string $filePath = '',
        string $analysisType = 'comprehensive',
        array $options = [],
    ): array {
        return $this->analyze($code, $filePath, $analysisType, $options);
    }

    /**
     * Analyze code content.
     *
     * @param string $code         Code content to analyze
     * @param string $filePath     Optional file path for context
     * @param string $analysisType Type of analysis (comprehensive, quality, security, etc.)
     * @param array  $options      Additional analysis options
     *
     * @throws AnalysisException    When analysis fails
     * @throws InvalidCodeException When code is invalid
     *
     * @return array Analysis results with success/error status
     */
    public function analyze(
        string $code,
        string $filePath = '',
        string $analysisType = 'comprehensive',
        array $options = [],
    ): array {
        try {
            $this->validateCode($code);

            $cacheKey = $this->generateCacheKey($code, $filePath, $analysisType, $options);

            // Check cache first
            if ($cachedResult = $this->cacheService->get($cacheKey)) {
                $this->logger->debug('Using cached analysis result', [
                    'file_path' => $filePath,
                    'analysis_type' => $analysisType,
                ]);

                return $cachedResult;
            }

            $this->logger->info('Starting code analysis', [
                'file_path' => $filePath,
                'analysis_type' => $analysisType,
                'code_length' => \strlen($code),
            ]);

            $result = $this->performAnalysis($code, $filePath, $analysisType, $options);

            // Cache the result
            $this->cacheService->set($cacheKey, $result, self::DEFAULT_CACHE_TTL);

            $this->logger->info('Analysis completed successfully', [
                'file_path' => $filePath,
                'total_issues' => $result['summary']['total_issues'],
            ]);

            return $result;
        } catch (AIProviderException $e) {
            $this->logger->error('AI Provider error during analysis', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
                'provider' => $e->getProvider(),
            ]);

            throw new AnalysisException('Analysis failed due to AI provider error: ' . $e->getMessage(), $e->getCode(), $e);
        } catch (InvalidCodeException $e) {
            $this->logger->warning('Invalid code provided for analysis', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } catch (\Throwable $e) {
            $this->logger->error('Unexpected error during analysis', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new AnalysisException('Analysis failed due to unexpected error: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Analyze a specific file with error handling.
     *
     * @param string $filePath     Path to the file to analyze
     * @param string $analysisType Type of analysis to perform
     * @param array  $options      Additional options
     *
     * @throws FileException     When file operations fail
     * @throws AnalysisException When analysis fails
     *
     * @return array Analysis results
     */
    public function analyzeFile(string $filePath, string $analysisType = 'comprehensive', array $options = []): array
    {
        try {
            $this->validateFile($filePath);

            $code = file_get_contents($filePath);
            if ($code === false) {
                throw FileException::fileNotReadable($filePath);
            }

            return $this->analyze($code, $filePath, $analysisType, $options);
        } catch (FileException $e) {
            $this->logger->error('File error during analysis', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Analyze multiple files in a directory.
     *
     * @param string $directoryPath Path to directory to analyze
     * @param array  $options       Options including file filters
     *
     * @throws FileException     When directory operations fail
     * @throws AnalysisException When analysis fails
     *
     * @return array Combined analysis results
     */
    public function analyzeDirectory(string $directoryPath, array $options = []): array
    {
        if (!is_dir($directoryPath)) {
            throw FileException::directoryNotFound($directoryPath);
        }

        $maxFiles = $options['max_files'] ?? 100;
        $excludePatterns = $options['exclude_patterns'] ?? ['vendor/', 'var/', 'node_modules/'];
        $recursive = $options['recursive'] ?? true;

        try {
            $finder = new Finder();
            $finder->files()
                ->name('*.php')
                ->in($directoryPath);

            if (!$recursive) {
                $finder->depth('== 0');
            }

            foreach ($excludePatterns as $pattern) {
                $finder->notPath($pattern);
            }

            if ($finder->count() > $maxFiles) {
                $this->logger->warning('Directory contains too many files, limiting analysis', [
                    'directory' => $directoryPath,
                    'total_files' => $finder->count(),
                    'max_files' => $maxFiles,
                ]);
            }

            $results = [
                'directory' => $directoryPath,
                'files_analyzed' => 0,
                'files_with_issues' => 0,
                'total_issues' => 0,
                'analysis_timestamp' => new \DateTimeImmutable(),
                'results' => [],
            ];

            $filesProcessed = 0;
            foreach ($finder as $file) {
                if ($filesProcessed >= $maxFiles) {
                    break;
                }

                try {
                    $fileResult = $this->analyzeFile($file->getRealPath());
                    $results['results'][$file->getRelativePathname()] = $fileResult;
                    ++$results['files_analyzed'];

                    if ($fileResult['summary']['total_issues'] > 0) {
                        ++$results['files_with_issues'];
                        $results['total_issues'] += $fileResult['summary']['total_issues'];
                    }
                } catch (\Exception $e) {
                    $this->logger->warning('Failed to analyze file in directory', [
                        'file' => $file->getRealPath(),
                        'error' => $e->getMessage(),
                    ]);

                    $results['results'][$file->getRelativePathname()] = [
                        'error' => $e->getMessage(),
                        'success' => false,
                    ];
                }

                ++$filesProcessed;
            }

            $this->logger->info('Directory analysis completed', [
                'directory' => $directoryPath,
                'files_analyzed' => $results['files_analyzed'],
                'total_issues' => $results['total_issues'],
            ]);

            return $results;
        } catch (\Throwable $e) {
            $this->logger->error('Directory analysis failed', [
                'directory' => $directoryPath,
                'error' => $e->getMessage(),
            ]);

            throw new AnalysisException('Failed to analyze directory: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Validate code before analysis.
     */
    private function validateCode(string $code): void
    {
        if (trim($code) === '') {
            throw InvalidCodeException::emptySyntax();
        }

        // Basic PHP syntax check
        if (!str_contains($code, '<?php') && !str_contains($code, '<?=')) {
            // For fragments, add PHP tags temporarily for validation
            $testCode = "<?php\n" . $code;
        } else {
            $testCode = $code;
        }

        $output = [];
        $returnVar = 0;
        $tempFile = tempnam(sys_get_temp_dir(), 'php_syntax_check_');

        try {
            file_put_contents($tempFile, $testCode);
            exec("php -l \"$tempFile\" 2>&1", $output, $returnVar);

            if ($returnVar !== 0) {
                $error = implode("\n", $output);
                throw InvalidCodeException::invalidSyntax($error);
            }
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * Validate file before analysis.
     */
    private function validateFile(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw FileException::fileNotFound($filePath);
        }

        if (!is_readable($filePath)) {
            throw FileException::fileNotReadable($filePath);
        }

        $fileSize = filesize($filePath);
        if ($fileSize > self::MAX_FILE_SIZE) {
            throw FileException::fileTooBig($filePath, self::MAX_FILE_SIZE, $fileSize);
        }

        $extension = pathinfo($filePath, \PATHINFO_EXTENSION);
        if (!\in_array(strtolower($extension), self::SUPPORTED_EXTENSIONS, true)) {
            throw InvalidCodeException::unsupportedFileType($filePath, self::SUPPORTED_EXTENSIONS);
        }
    }

    /**
     * Perform the actual analysis based on type.
     */
    private function performAnalysis(string $code, string $filePath, string $analysisType, array $options): array
    {
        $result = [
            'success' => true,
            'file_path' => $filePath,
            'analysis_type' => $analysisType,
            'timestamp' => new \DateTimeImmutable(),
            'summary' => [
                'total_issues' => 0,
                'critical' => 0,
                'warning' => 0,
                'info' => 0,
                'quality_score' => 0.0,
                'security_score' => 0.0,
            ],
            'issues' => [],
            'metrics' => [
                'lines_analyzed' => substr_count($code, "\n") + 1,
                'complexity_score' => 0.0,
                'maintainability_index' => 0,
            ],
        ];

        $analyzers = $this->getAnalyzersForType($analysisType, $options);

        foreach ($analyzers as $analyzer) {
            try {
                $analyzerResult = $analyzer->analyze($code, $filePath);
                $this->mergeAnalysisResults($result, $analyzerResult);
            } catch (AIProviderException $e) {
                // Re-throw AI provider exceptions as they indicate infrastructure issues
                throw $e;
            } catch (\Exception $e) {
                $this->logger->warning('Analyzer failed', [
                    'analyzer' => $analyzer::class,
                    'error' => $e->getMessage(),
                    'file_path' => $filePath,
                ]);

                // Continue with other analyzers even if one fails
                continue;
            }
        }

        return $result;
    }

    /**
     * Get analyzers based on analysis type.
     */
    private function getAnalyzersForType(string $analysisType, array $options): array
    {
        $allAnalyzers = [
            'quality' => $this->qualityAnalyzer,
            'security' => $this->securityAnalyzer,
            'performance' => $this->performanceAnalyzer,
            'documentation' => $this->documentationAnalyzer,
        ];

        if ($analysisType === 'comprehensive') {
            return array_values($allAnalyzers);
        }

        if (isset($options['analyzers'])) {
            $requestedAnalyzers = [];
            foreach ($options['analyzers'] as $analyzerName) {
                if (isset($allAnalyzers[$analyzerName])) {
                    $requestedAnalyzers[] = $allAnalyzers[$analyzerName];
                }
            }

            return $requestedAnalyzers;
        }

        return isset($allAnalyzers[$analysisType]) ? [$allAnalyzers[$analysisType]] : [];
    }

    /**
     * Merge analyzer results into main result.
     */
    private function mergeAnalysisResults(array &$result, array $analyzerResult): void
    {
        if (isset($analyzerResult['issues']) && \is_array($analyzerResult['issues'])) {
            $result['issues'] = array_merge($result['issues'], $analyzerResult['issues']);
            $result['summary']['total_issues'] += \count($analyzerResult['issues']);

            // Count issues by severity
            foreach ($analyzerResult['issues'] as $issue) {
                $severity = $issue['severity'] ?? 'info';
                if (isset($result['summary'][$severity])) {
                    ++$result['summary'][$severity];
                }
            }
        }

        // Merge metrics and scores
        if (isset($analyzerResult['metrics'])) {
            $result['metrics'] = array_merge($result['metrics'], $analyzerResult['metrics']);
        }

        if (isset($analyzerResult['quality_score'])) {
            $result['summary']['quality_score'] = max($result['summary']['quality_score'], $analyzerResult['quality_score']);
        }

        if (isset($analyzerResult['security_score'])) {
            $result['summary']['security_score'] = max($result['summary']['security_score'], $analyzerResult['security_score']);
        }
    }

    /**
     * Generate cache key for analysis.
     */
    private function generateCacheKey(string $code, string $filePath, string $analysisType, array $options): string
    {
        $data = [
            'code_hash' => md5($code),
            'file_path' => $filePath,
            'analysis_type' => $analysisType,
            'options' => $options,
        ];

        return 'code_analysis_' . md5(serialize($data));
    }

    /**
     * Get available analyzer names.
     */
    public function getAnalyzerNames(): array
    {
        return ['quality', 'security', 'performance', 'documentation'];
    }

    /**
     * Get AI-powered suggestions for code improvements.
     */
    public function getAISuggestions(string $code, array $issues = []): array
    {
        try {
            $prompt = "Analyze this code and provide improvement suggestions:\n\n{$code}\n\nIssues found:\n" . json_encode($issues);
            $response = $this->aiManager->request($prompt, ['type' => 'code_suggestions']);

            return ['suggestions' => $response];
        } catch (\Exception $e) {
            // AI suggestions are optional, return empty array on failure
            return [];
        }
    }
}

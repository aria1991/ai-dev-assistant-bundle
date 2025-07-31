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

namespace Aria1991\AIDevAssistantBundle\Tests\Unit\Service;

use Aria1991\AIDevAssistantBundle\Exception\AIProviderException;
use Aria1991\AIDevAssistantBundle\Exception\AnalysisException;
use Aria1991\AIDevAssistantBundle\Service\AIManager;
use Aria1991\AIDevAssistantBundle\Service\Analyzer\DocumentationAnalyzer;
use Aria1991\AIDevAssistantBundle\Service\Analyzer\PerformanceAnalyzer;
use Aria1991\AIDevAssistantBundle\Service\Analyzer\QualityAnalyzer;
use Aria1991\AIDevAssistantBundle\Service\Analyzer\SecurityAnalyzer;
use Aria1991\AIDevAssistantBundle\Service\CacheService;
use Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer
 */
final class CodeAnalyzerTest extends TestCase
{
    private CodeAnalyzer $codeAnalyzer;
    private MockObject&AIManager $aiManager;
    private MockObject&CacheService $cacheService;
    private MockObject&LoggerInterface $logger;
    private MockObject&QualityAnalyzer $qualityAnalyzer;
    private MockObject&SecurityAnalyzer $securityAnalyzer;
    private MockObject&PerformanceAnalyzer $performanceAnalyzer;
    private MockObject&DocumentationAnalyzer $documentationAnalyzer;

    protected function setUp(): void
    {
        $this->aiManager = $this->createMock(AIManager::class);
        $this->cacheService = $this->createMock(CacheService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->qualityAnalyzer = $this->createMock(QualityAnalyzer::class);
        $this->securityAnalyzer = $this->createMock(SecurityAnalyzer::class);
        $this->performanceAnalyzer = $this->createMock(PerformanceAnalyzer::class);
        $this->documentationAnalyzer = $this->createMock(DocumentationAnalyzer::class);

        $this->codeAnalyzer = new CodeAnalyzer(
            $this->aiManager,
            $this->cacheService,
            $this->logger,
            $this->qualityAnalyzer,
            $this->securityAnalyzer,
            $this->performanceAnalyzer,
            $this->documentationAnalyzer
        );
    }

    public function testAnalyzeValidCodeReturnsResult(): void
    {
        $code = '<?php echo "Hello World";';
        $filePath = 'test.php';

        $expectedResult = [
            'success' => true,
            'summary' => [
                'total_issues' => 0,
                'critical' => 0,
                'warning' => 0,
                'info' => 0,
                'quality_score' => 9.5,
                'security_score' => 10.0,
            ],
            'issues' => [],
            'metrics' => [
                'lines_analyzed' => 1,
                'complexity_score' => 1.0,
                'maintainability_index' => 95,
            ],
        ];

        $this->cacheService
            ->expects(self::once())
            ->method('get')
            ->willReturn(null);

        $this->qualityAnalyzer
            ->expects(self::once())
            ->method('analyze')
            ->with($code, $filePath)
            ->willReturn($expectedResult);

        $this->cacheService
            ->expects(self::once())
            ->method('set')
            ->with(self::isType('string'), $expectedResult, 3600);

        $result = $this->codeAnalyzer->analyze($code, $filePath);

        self::assertSame($expectedResult, $result);
    }

    public function testAnalyzeWithEmptyCodeThrowsException(): void
    {
        $this->expectException(AnalysisException::class);
        $this->expectExceptionMessage('Code cannot be empty');

        $this->codeAnalyzer->analyze('', 'test.php');
    }

    public function testAnalyzeWithInvalidCodeThrowsException(): void
    {
        $code = '<?php invalid syntax here';

        $this->expectException(AnalysisException::class);
        $this->expectExceptionMessage('Invalid PHP syntax');

        $this->codeAnalyzer->analyze($code, 'test.php');
    }

    public function testAnalyzeFileWithNonExistentFileThrowsException(): void
    {
        $this->expectException(AnalysisException::class);
        $this->expectExceptionMessage('File not found');

        $this->codeAnalyzer->analyzeFile('/nonexistent/file.php');
    }

    public function testAnalyzeHandlesAIProviderException(): void
    {
        $code = '<?php echo "Hello World";';

        $this->cacheService
            ->expects(self::once())
            ->method('get')
            ->willReturn(null);

        $this->qualityAnalyzer
            ->expects(self::once())
            ->method('analyze')
            ->willThrowException(new AIProviderException('API rate limit exceeded'));

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with('AI Provider error during analysis', self::isType('array'));

        $this->expectException(AnalysisException::class);
        $this->expectExceptionMessage('Analysis failed due to AI provider error: API rate limit exceeded');

        $this->codeAnalyzer->analyze($code, 'test.php');
    }

    public function testAnalyzeUsesCache(): void
    {
        $code = '<?php echo "Hello World";';
        $cachedResult = ['cached' => true];

        $this->cacheService
            ->expects(self::once())
            ->method('get')
            ->willReturn($cachedResult);

        $this->qualityAnalyzer
            ->expects(self::never())
            ->method('analyze');

        $result = $this->codeAnalyzer->analyze($code, 'test.php');

        self::assertSame($cachedResult, $result);
    }

    public function testAnalyzeDirectoryWithValidPath(): void
    {
        $directory = __DIR__ . '/../../fixtures';

        // Create fixtures directory if it doesn't exist
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
            file_put_contents($directory . '/test.php', '<?php echo "test";');
        }

        $result = $this->codeAnalyzer->analyzeDirectory($directory);

        self::assertIsArray($result);
        self::assertArrayHasKey('files_analyzed', $result);
        self::assertArrayHasKey('total_issues', $result);
        self::assertArrayHasKey('results', $result);
    }

    public function testAnalyzeDirectoryWithInvalidPathThrowsException(): void
    {
        $this->expectException(AnalysisException::class);
        $this->expectExceptionMessage('Directory not found');

        $this->codeAnalyzer->analyzeDirectory('/nonexistent/directory');
    }

    public function testAnalyzeWithSpecificAnalyzers(): void
    {
        $code = '<?php echo "Hello World";';
        $options = ['analyzers' => ['security', 'performance']];

        $this->cacheService
            ->expects(self::once())
            ->method('get')
            ->willReturn(null);

        $this->securityAnalyzer
            ->expects(self::once())
            ->method('analyze')
            ->willReturn(['security' => 'results']);

        $this->performanceAnalyzer
            ->expects(self::once())
            ->method('analyze')
            ->willReturn(['performance' => 'results']);

        // Quality and documentation analyzers should not be called
        $this->qualityAnalyzer
            ->expects(self::never())
            ->method('analyze');

        $this->documentationAnalyzer
            ->expects(self::never())
            ->method('analyze');

        $this->codeAnalyzer->analyze($code, 'test.php', 'custom', $options);
    }
}

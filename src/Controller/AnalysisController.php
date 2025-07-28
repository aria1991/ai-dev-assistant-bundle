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

namespace Aria1991\AIDevAssistantBundle\Controller;

use Aria1991\AIDevAssistantBundle\Service\CodeAnalyzer;
use Aria1991\AIDevAssistantBundle\Service\RateLimiter;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for AI code analysis endpoints.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
#[Route('/ai-dev-assistant', name: 'ai_dev_assistant_')]
final class AnalysisController extends AbstractController
{
    public function __construct(
        private readonly CodeAnalyzer $codeAnalyzer,
        private readonly RateLimiter $rateLimiter,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Analyze code via API endpoint.
     */
    #[Route('/analyze', name: 'analyze', methods: ['POST'])]
    public function analyze(Request $request): JsonResponse
    {
        try {
            // Rate limiting
            $clientIp = $request->getClientIp() ?? 'unknown';
            if (!$this->rateLimiter->isAllowed($clientIp, 60, 60)) { // 60 requests per minute
                return new JsonResponse([
                    'error' => 'Rate limit exceeded. Please try again later.',
                ], Response::HTTP_TOO_MANY_REQUESTS);
            }

            // Validate request
            $data = json_decode($request->getContent(), true);
            if (!$data || !isset($data['code'])) {
                return new JsonResponse([
                    'error' => 'Invalid request. Code parameter is required.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $code = $data['code'];
            $filename = $data['filename'] ?? '';
            $enabledAnalyzers = $data['analyzers'] ?? null;

            // Validate code size
            if (strlen($code) > 1048576) { // 1MB limit
                return new JsonResponse([
                    'error' => 'Code too large. Maximum size is 1MB.',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Perform analysis
            $results = $this->codeAnalyzer->analyzeCode($code, $filename, $enabledAnalyzers);

            $this->logger->info('Code analysis completed', [
                'filename' => $filename,
                'client_ip' => $clientIp,
                'risk_score' => $results['risk_score'] ?? 'unknown',
                'total_issues' => $results['summary']['total_issues'] ?? 0,
            ]);

            return new JsonResponse($results);

        } catch (\Exception $e) {
            $this->logger->error('Analysis endpoint error', [
                'error' => $e->getMessage(),
                'client_ip' => 'unknown',
            ]);

            return new JsonResponse([
                'error' => 'Analysis failed: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Analyze file via API endpoint.
     */
    #[Route('/analyze-file', name: 'analyze_file', methods: ['POST'])]
    public function analyzeFile(Request $request): JsonResponse
    {
        try {
            // Rate limiting
            $clientIp = $request->getClientIp() ?? 'unknown';
            if (!$this->rateLimiter->isAllowed($clientIp, 30, 60)) { // 30 file requests per minute
                return new JsonResponse([
                    'error' => 'Rate limit exceeded. Please try again later.',
                ], Response::HTTP_TOO_MANY_REQUESTS);
            }

            // Validate request
            $data = json_decode($request->getContent(), true);
            if (!$data || !isset($data['file_path'])) {
                return new JsonResponse([
                    'error' => 'Invalid request. file_path parameter is required.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $filePath = $data['file_path'];
            $enabledAnalyzers = $data['analyzers'] ?? null;

            // Security check - only allow certain file extensions
            $allowedExtensions = ['php', 'twig', 'yaml', 'yml', 'json'];
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            
            if (!in_array($extension, $allowedExtensions, true)) {
                return new JsonResponse([
                    'error' => 'File type not supported. Allowed: ' . implode(', ', $allowedExtensions),
                ], Response::HTTP_BAD_REQUEST);
            }

            // Perform analysis
            $results = $this->codeAnalyzer->analyzeFile($filePath, $enabledAnalyzers);

            $this->logger->info('File analysis completed', [
                'file_path' => $filePath,
                'client_ip' => $clientIp,
                'risk_score' => $results['risk_score'] ?? 'unknown',
                'total_issues' => $results['summary']['total_issues'] ?? 0,
            ]);

            return new JsonResponse($results);

        } catch (\Exception $e) {
            $this->logger->error('File analysis endpoint error', [
                'error' => $e->getMessage(),
                'client_ip' => 'unknown',
            ]);

            return new JsonResponse([
                'error' => 'File analysis failed: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get available analyzers.
     */
    #[Route('/analyzers', name: 'analyzers', methods: ['GET'])]
    public function getAnalyzers(): JsonResponse
    {
        try {
            $analyzers = $this->codeAnalyzer->getAnalyzerNames();
            
            return new JsonResponse([
                'analyzers' => $analyzers,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to get analyzers: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Health check endpoint.
     */
    #[Route('/health', name: 'health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'analyzers' => $this->codeAnalyzer->getAnalyzerNames(),
        ]);
    }
}


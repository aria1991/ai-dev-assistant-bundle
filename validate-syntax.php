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

$files = [
    'src/DTO/AnalysisRequest.php',
    'src/DTO/AnalysisResult.php',
    'src/Event/PreAnalysisEvent.php',
    'src/Event/PostAnalysisEvent.php',
    'src/Event/AIProviderFailureEvent.php',
    'src/Exception/AIDevAssistantException.php',
    'src/Exception/AIProviderException.php',
    'src/Exception/AnalysisException.php',
    'src/Exception/ConfigurationException.php',
    'src/DependencyInjection/Compiler/AnalyzerPass.php',
    'src/DependencyInjection/Compiler/AIProviderPass.php',
    'src/Command/HealthCheckCommand.php',
    'src/Service/AdvancedCacheService.php',
    'src/DataCollector/AIAnalysisDataCollector.php',
];

$errors = [];

foreach ($files as $file) {
    if (!file_exists($file)) {
        $errors[] = "File not found: $file";
        continue;
    }

    $output = [];
    $returnVar = 0;
    exec("php -l \"$file\" 2>&1", $output, $returnVar);

    if ($returnVar !== 0) {
        $errors[] = "Syntax error in $file: " . implode("\n", $output);
    } else {
        echo "✓ $file - OK\n";
    }
}

if (!empty($errors)) {
    echo "\nErrors found:\n";
    foreach ($errors as $error) {
        echo "✗ $error\n";
    }
    exit(1);
}

echo "\nAll files passed syntax validation!\n";
exit(0);

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

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'declare_strict_types' => true,
        'header_comment' => [
            'header' => <<<EOF
This file is part of the AI Development Assistant Bundle.

(c) Aria Vahidi <aria.vahidi2020@gmail.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF,
        ],
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
        'no_unused_imports' => true,
        'phpdoc_order' => true,
        'phpdoc_summary' => false,
        'concat_space' => ['spacing' => 'one'],
        'yoda_style' => false,
    ])
    ->setFinder($finder)
;

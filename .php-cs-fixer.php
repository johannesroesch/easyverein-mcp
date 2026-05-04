<?php

declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS2.0'                  => true,
        '@PHP82Migration'             => true,
        'declare_strict_types'        => true,
        'ordered_imports'             => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'           => true,
        'single_quote'                => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/src')
            ->in(__DIR__ . '/tests')
    );

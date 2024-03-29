<?php

$finder = PhpCsFixer\Finder::create()
    // ->exclude('somedir')
    // ->notPath('src/Symfony/Component/Translation/Tests/fixtures/resources.php')
    ->in(__DIR__);
$config = new PhpCsFixer\Config();
return $config->setRules([
        '@PSR2'                  => true,
        'array_syntax'           => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'default'   => 'single_space',
            'operators' => [
             '=>' => 'align_single_space_minimal',
            ],
        ],
        'concat_space'                => ['spacing' => 'one'],
        'explicit_string_variable'    => true,
        'no_closing_tag'              => true,
        'no_empty_statement'          => true,
        'no_extra_blank_lines'        => true,
        'no_unused_imports'           => true,
        'no_whitespace_in_blank_line' => true,
        'ordered_class_elements'      => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'property_public_static',
                'property_protected_static',
                'property_private_static',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
                'method_public_static',
                'method_protected_static',
                'method_private_static',
            ],
            'sort_algorithm' => 'alpha',
            ],
        'ordered_imports'            => true,
        'single_line_after_imports'  => true,
        'ternary_operator_spaces'    => true,
        'ternary_to_null_coalescing' => true,
    ])
    ->setFinder($finder);

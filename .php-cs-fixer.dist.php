<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
    // deliberately unparsable: it is the fixture for the parsing-error assertions
    ->notPath('tests/_fixtures/parse_error/BrokenService.php');

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    // the project targets PHP 8.0, but contributors may run the fixer on a newer runtime
    ->setUnsupportedPhpVersionAllowed(true)
    ->setRules([
        '@PER-CS' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP8x0Migration:risky' => true,
        '@PSR2' => true,
        '@DoctrineAnnotation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'fully_qualified_strict_types' => true,
        'dir_constant' => true,
        'heredoc_to_nowdoc' => true,
        'linebreak_after_opening_tag' => true,
        'blank_line_after_opening_tag' => false,
        'modernize_types_casting' => true,
        'multiline_whitespace_before_semicolons' => true,
        'no_unreachable_default_argument_value' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => false],
        'phpdoc_order' => true,
        'declare_strict_types' => true,
        'psr_autoloading' => true,
        'no_php4_constructor' => true,
        'semicolon_after_instruction' => true,
        'align_multiline_comment' => true,
        'general_phpdoc_annotation_remove' => ['annotations' => ['author', 'package']],
        'list_syntax' => ['syntax' => 'short'],
        'phpdoc_to_comment' => false,
        'php_unit_method_casing' => ['case' => 'snake_case'],
        'function_to_constant' => false,
        'php_unit_data_provider_static' => true,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        'phpdoc_array_type' => true,
    ]);

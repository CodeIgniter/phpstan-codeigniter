<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) 2023 CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use CodeIgniter\CodingStandard\CodeIgniter4;
use Nexus\CsConfig\Factory;
use PhpCsFixer\Finder;
use PhpCsFixerCustomFixers\Fixer;
use PhpCsFixerCustomFixers\Fixers;

$finder = Finder::create()
    ->files()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->append([
        __FILE__,
        __DIR__ . '/bootstrap.php',
    ]);

$overrides = [
    'declare_strict_types'        => true,
    'php_unit_data_provider_name' => [
        'prefix' => 'provide',
        'suffix' => 'Cases',
    ],
    'php_unit_test_case_static_method_calls' => [
        'call_type' => 'self',
        'methods'   => [],
        'target'    => '10.0',
    ],
    'phpdoc_to_param_type'    => true,
    'phpdoc_to_property_type' => true,
    'phpdoc_to_return_type'   => true,
    'single_line_empty_body'  => true,
    'void_return'             => true,
];

$options = [
    'cacheFile'    => 'build/.php-cs-fixer.cache',
    'finder'       => $finder,
    'customFixers' => new Fixers(),
    'customRules'  => [
        Fixer\FunctionParameterSeparationFixer::name()    => true,
        Fixer\NoCommentedOutCodeFixer::name()             => true,
        Fixer\NoTrailingCommaInSinglelineFixer::name()    => true,
        Fixer\NoUselessCommentFixer::name()               => true,
        Fixer\NoUselessParenthesisFixer::name()           => true,
        Fixer\NoUselessWriteVisibilityFixer::name()       => true,
        Fixer\PhpUnitAssertArgumentsOrderFixer::name()    => true,
        Fixer\PhpUnitNoUselessReturnFixer::name()         => true,
        Fixer\PhpdocNoIncorrectVarAnnotationFixer::name() => true,
        Fixer\PhpdocSelfAccessorFixer::name()             => true,
        Fixer\PhpdocTypesCommaSpacesFixer::name()         => true,
        Fixer\PhpdocVarAnnotationToAssertFixer::name()    => true,
        Fixer\PromotedConstructorPropertyFixer::name()    => ['promote_only_existing_properties' => true],
    ],
];

return Factory::create(new CodeIgniter4(), $overrides, $options)->forLibrary(
    'CodeIgniter 4 framework',
    'CodeIgniter Foundation',
    'admin@codeigniter.com',
    2023,
);

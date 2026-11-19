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

namespace CodeIgniter\PHPStan\Tests\Rules\Database;

use CodeIgniter\PHPStan\Rules\Database\ModelWriteAllowedFieldsRule;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @extends RuleTestCase<ModelWriteAllowedFieldsRule>
 *
 * @internal
 */
#[Group('static-analysis')]
final class ModelWriteAllowedFieldsRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesProvider;

    protected function getRule(): Rule
    {
        return new ModelWriteAllowedFieldsRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../data/rules/model-write-allowed-fields.php'], [
            [
                'Field "titel" is not in CodeIgniter\PHPStan\Tests\Fixtures\Models\AllowedFieldsModel::$allowedFields and is dropped by insert().',
                30,
            ],
            [
                'Field "body" is not in CodeIgniter\PHPStan\Tests\Fixtures\Models\AllowedFieldsModel::$allowedFields and is dropped by save().',
                31,
            ],
            [
                'Field "body" is not in CodeIgniter\PHPStan\Tests\Fixtures\Models\AllowedFieldsModel::$allowedFields and is dropped by update().',
                32,
            ],
        ]);
    }
}

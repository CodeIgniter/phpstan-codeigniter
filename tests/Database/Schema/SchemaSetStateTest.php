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

namespace CodeIgniter\PHPStan\Tests\Database\Schema;

use CodeIgniter\PHPStan\Database\Schema\Column;
use CodeIgniter\PHPStan\Database\Schema\Schema;
use CodeIgniter\PHPStan\Database\Schema\Table;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Group('unit')]
final class SchemaSetStateTest extends TestCase
{
    public function testSurvivesVarExportFileCacheRoundTrip(): void
    {
        $schema = new Schema(hash: 'fingerprint', tables: [
            'users' => new Table(name: 'users', columns: [
                'id'   => new Column(name: 'id', type: 'INTEGER', nullable: false, primaryKey: true, default: null),
                'name' => new Column(name: 'name', type: 'VARCHAR', nullable: false, primaryKey: false, default: 'guest'),
            ]),
        ]);

        $file = dirname(__DIR__, 3) . '/tmp/' . uniqid('schema-cache-', true) . '.php';
        file_put_contents($file, '<?php return ' . var_export($schema, true) . ';');

        try {
            $restored = include $file;
        } finally {
            unlink($file);
        }

        self::assertSame(var_export($schema, true), var_export($restored, true));
    }
}

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

namespace CodeIgniter\PHPStan\Tests\Fixtures\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

/**
 * Stands in for a migration that cannot run on SQLite (e.g. raw vendor SQL).
 */
final class FailBlogTable extends Migration
{
    public function up(): void
    {
        throw new RuntimeException('Simulated non-portable migration.');
    }

    public function down(): void
    {
        // Nothing to undo: up() never completes.
    }
}

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

final class FailBlogTable extends Migration
{
    public function up(): void {}

    public function down(): void {}
}

final class CreateBlogPosts extends Migration
{
    public function up(): void {}

    public function down(): void {}
}

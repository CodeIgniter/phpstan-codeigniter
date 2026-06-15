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

final class CreateBlogGroups extends Migration
{
    protected $DBGroup = 'other';

    public function up(): void
    {
        $this->forge->addField([
            'id'   => ['type' => 'INTEGER', 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('blog_groups');
    }

    public function down(): void
    {
        $this->forge->dropTable('blog_groups');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingColumnsToRulesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('rules', [
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'after' => 'id',
            ],
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'after' => 'name',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('rules', ['name', 'category']);
    }
} 
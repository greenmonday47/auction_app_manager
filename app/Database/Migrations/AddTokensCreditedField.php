<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTokensCreditedField extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transactions', [
            'tokens_credited' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'comment' => 'Whether tokens have been credited for this transaction'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', 'tokens_credited');
    }
} 
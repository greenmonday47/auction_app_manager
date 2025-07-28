<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentFieldsToTransactionsTable extends Migration
{
    public function up()
    {
        // Add new fields to transactions table
        $fields = [
            'transaction_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'note',
            ],
            'payment_type' => [
                'type' => 'ENUM',
                'constraint' => ['topup', 'withdrawal'],
                'null' => true,
                'after' => 'transaction_id',
            ],
        ];

        $this->forge->addColumn('transactions', $fields);

        // Update status enum to include success and failed
        $this->forge->modifyColumn('transactions', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected', 'success', 'failed'],
                'default' => 'pending',
            ],
        ]);
    }

    public function down()
    {
        // Remove added columns
        $this->forge->dropColumn('transactions', ['transaction_id', 'payment_type']);

        // Revert status enum
        $this->forge->modifyColumn('transactions', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected'],
                'default' => 'pending',
            ],
        ]);
    }
} 
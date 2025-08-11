<?php

namespace App\Controllers\Api;

use App\Models\PaymentModel;
use App\Models\UserModel;

class TestController extends BaseApiController
{
    protected $format = 'json';

    public function testDatabase()
    {
        try {
            // Test database connection
            $db = \Config\Database::connect();
            $result = $db->query('SELECT 1 as test')->getRow();
            
            if (!$result) {
                return $this->errorResponse('Database connection failed', 500);
            }

            // Test transactions table
            $paymentModel = new PaymentModel();
            $tableExists = $db->tableExists('transactions');
            
            if (!$tableExists) {
                return $this->errorResponse('Transactions table does not exist', 500);
            }

            // Test user exists
            $userModel = new UserModel();
            $user = $userModel->find(2); // Test with user_id 2
            
            if (!$user) {
                return $this->errorResponse('User with ID 2 does not exist', 404);
            }

            // Test table structure
            $fields = $db->getFieldNames('transactions');
            
            return $this->successResponse([
                'database_connection' => 'OK',
                'transactions_table_exists' => $tableExists,
                'user_exists' => !empty($user),
                'table_fields' => $fields,
                'user_data' => $user
            ], 'Database test completed successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Database test failed: ' . $e->getMessage(), 500);
        }
    }
} 
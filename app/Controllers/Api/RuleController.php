<?php

namespace App\Controllers\Api;

use App\Models\RuleModel;

class RuleController extends BaseApiController
{
    public function getRules()
    {
        $rules = $this->ruleModel->getCurrentRules();
        
        return $this->successResponse([
            'content' => $rules,
            'last_updated' => $this->ruleModel->orderBy('last_updated', 'DESC')->first()['last_updated'] ?? null
        ], 'Rules retrieved successfully');
    }
} 
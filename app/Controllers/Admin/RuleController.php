<?php

namespace App\Controllers\Admin;

use App\Models\RuleModel;

class RuleController extends BaseAdminController
{
    protected $ruleModel;

    public function __construct()
    {
        parent::__construct();
        $this->ruleModel = new RuleModel();
    }

    public function index()
    {
        $this->requireAuth();

        $rules = $this->ruleModel->orderBy('category', 'ASC')->orderBy('name', 'ASC')->findAll();

        // Get statistics
        $stats = [
            'total_rules' => $this->ruleModel->countAll(),
            'active_rules' => $this->ruleModel->where('is_active', 1)->countAllResults(),
            'system_rules' => $this->ruleModel->where('is_custom', 0)->countAllResults(),
            'custom_rules' => $this->ruleModel->where('is_custom', 1)->countAllResults(),
        ];

        // Get current settings
        $auctionSettings = [
            'min_bid_increment' => $this->ruleModel->getRuleValue('min_bid_increment', 1000),
            'max_auction_duration' => $this->ruleModel->getRuleValue('max_auction_duration', 24),
            'auto_extend_minutes' => $this->ruleModel->getRuleValue('auto_extend_minutes', 5),
        ];

        $userSettings = [
            'min_tokens_to_bid' => $this->ruleModel->getRuleValue('min_tokens_to_bid', 1),
            'max_concurrent_bids' => $this->ruleModel->getRuleValue('max_concurrent_bids', 5),
            'token_expiry_days' => $this->ruleModel->getRuleValue('token_expiry_days', 365),
        ];

        $data = [
            'title' => 'System Rules & Configuration',
            'rules' => $rules,
            'total_rules' => count($rules),
            'stats' => $stats,
            'auction_settings' => $auctionSettings,
            'user_settings' => $userSettings,
        ];

        return $this->render('rules/index', $data);
    }

    public function add()
    {
        $this->requireAuth();

        $name = $this->request->getPost('name');
        $key = $this->request->getPost('key');
        $category = $this->request->getPost('category');
        $value = $this->request->getPost('value');
        $description = $this->request->getPost('description');

        if (!$name || !$key || !$category || !$value || !$description) {
            $this->errorMessage('All fields are required');
            return redirect()->back();
        }

        // Check if key already exists
        if ($this->ruleModel->where('key', $key)->first()) {
            $this->errorMessage('Rule key already exists');
            return redirect()->back();
        }

        $ruleData = [
            'name' => $name,
            'key' => $key,
            'category' => $category,
            'value' => $value,
            'description' => $description,
            'is_active' => 1,
            'is_custom' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->ruleModel->insert($ruleData)) {
            $this->successMessage('Rule added successfully');
        } else {
            $this->errorMessage('Failed to add rule');
        }

        return redirect()->to('/admin/rules');
    }

    public function edit()
    {
        $this->requireAuth();

        $ruleId = $this->request->getPost('rule_id');
        $name = $this->request->getPost('name');
        $value = $this->request->getPost('value');
        $description = $this->request->getPost('description');

        if (!$ruleId || !$name || !$value || !$description) {
            $this->errorMessage('All fields are required');
            return redirect()->back();
        }

        $rule = $this->ruleModel->find($ruleId);
        if (!$rule) {
            $this->errorMessage('Rule not found');
            return redirect()->back();
        }

        $updateData = [
            'name' => $name,
            'value' => $value,
            'description' => $description,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->ruleModel->update($ruleId, $updateData)) {
            $this->successMessage('Rule updated successfully');
        } else {
            $this->errorMessage('Failed to update rule');
        }

        return redirect()->to('/admin/rules');
    }

    public function toggle($ruleId, $action)
    {
        $this->requireAuth();

        $rule = $this->ruleModel->find($ruleId);
        if (!$rule) {
            $this->errorMessage('Rule not found');
            return redirect()->to('/admin/rules');
        }

        $isActive = ($action === 'activate') ? 1 : 0;
        $statusText = $isActive ? 'activated' : 'deactivated';

        if ($this->ruleModel->update($ruleId, ['is_active' => $isActive])) {
            $this->successMessage("Rule {$statusText} successfully");
        } else {
            $this->errorMessage("Failed to {$action} rule");
        }

        return redirect()->to('/admin/rules');
    }

    public function delete($ruleId)
    {
        $this->requireAuth();

        $rule = $this->ruleModel->find($ruleId);
        if (!$rule) {
            $this->errorMessage('Rule not found');
            return redirect()->to('/admin/rules');
        }

        // Don't allow deletion of system rules
        if (!$rule['is_custom']) {
            $this->errorMessage('Cannot delete system rules');
            return redirect()->to('/admin/rules');
        }

        if ($this->ruleModel->delete($ruleId)) {
            $this->successMessage('Rule deleted successfully');
        } else {
            $this->errorMessage('Failed to delete rule');
        }

        return redirect()->to('/admin/rules');
    }

    public function updateAuctionSettings()
    {
        $this->requireAuth();

        $minBidIncrement = $this->request->getPost('min_bid_increment');
        $maxAuctionDuration = $this->request->getPost('max_auction_duration');
        $autoExtendMinutes = $this->request->getPost('auto_extend_minutes');

        if (!$minBidIncrement || !$maxAuctionDuration) {
            $this->errorMessage('Required fields are missing');
            return redirect()->back();
        }

        $settings = [
            'min_bid_increment' => $minBidIncrement,
            'max_auction_duration' => $maxAuctionDuration,
            'auto_extend_minutes' => $autoExtendMinutes,
        ];

        foreach ($settings as $key => $value) {
            $this->updateOrCreateRule($key, $value, 'auction');
        }

        $this->successMessage('Auction settings updated successfully');
        return redirect()->to('/admin/rules');
    }

    public function updateUserSettings()
    {
        $this->requireAuth();

        $minTokensToBid = $this->request->getPost('min_tokens_to_bid');
        $maxConcurrentBids = $this->request->getPost('max_concurrent_bids');
        $tokenExpiryDays = $this->request->getPost('token_expiry_days');

        if (!$minTokensToBid || !$maxConcurrentBids) {
            $this->errorMessage('Required fields are missing');
            return redirect()->back();
        }

        $settings = [
            'min_tokens_to_bid' => $minTokensToBid,
            'max_concurrent_bids' => $maxConcurrentBids,
            'token_expiry_days' => $tokenExpiryDays,
        ];

        foreach ($settings as $key => $value) {
            $this->updateOrCreateRule($key, $value, 'user');
        }

        $this->successMessage('User settings updated successfully');
        return redirect()->to('/admin/rules');
    }

    private function updateOrCreateRule($key, $value, $category)
    {
        $rule = $this->ruleModel->where('key', $key)->first();
        
        if ($rule) {
            // Update existing rule
            $this->ruleModel->update($rule['id'], [
                'value' => $value,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            // Create new rule
            $ruleData = [
                'name' => ucwords(str_replace('_', ' ', $key)),
                'key' => $key,
                'category' => $category,
                'value' => $value,
                'description' => ucwords(str_replace('_', ' ', $key)),
                'is_active' => 1,
                'is_custom' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $this->ruleModel->insert($ruleData);
        }
    }
} 
<?php

namespace App\Models;

use CodeIgniter\Model;

class RuleModel extends Model
{
    protected $table = 'rules';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name', 'category', 'key', 'value', 'description', 
        'is_active', 'is_custom', 'content', 'created_at', 'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[255]',
        'key' => 'required|min_length[2]|max_length[100]',
        'category' => 'required|min_length[2]|max_length[100]',
        'value' => 'required',
        'description' => 'required|min_length[10]',
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Rule name is required',
            'min_length' => 'Rule name must be at least 2 characters',
            'max_length' => 'Rule name cannot exceed 255 characters',
        ],
        'key' => [
            'required' => 'Rule key is required',
            'min_length' => 'Rule key must be at least 2 characters',
            'max_length' => 'Rule key cannot exceed 100 characters',
        ],
        'category' => [
            'required' => 'Rule category is required',
            'min_length' => 'Rule category must be at least 2 characters',
            'max_length' => 'Rule category cannot exceed 100 characters',
        ],
        'value' => [
            'required' => 'Rule value is required',
        ],
        'description' => [
            'required' => 'Rule description is required',
            'min_length' => 'Rule description must be at least 10 characters',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get a rule value by key with optional default
     */
    public function getRuleValue($key, $default = null)
    {
        $rule = $this->where('key', $key)
                    ->where('is_active', 1)
                    ->first();
        
        return $rule ? $rule['value'] : $default;
    }

    /**
     * Get all active rules
     */
    public function getActiveRules()
    {
        return $this->where('is_active', 1)
                   ->orderBy('category', 'ASC')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Get rules by category
     */
    public function getRulesByCategory($category)
    {
        return $this->where('category', $category)
                   ->where('is_active', 1)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Get system rules (non-custom)
     */
    public function getSystemRules()
    {
        return $this->where('is_custom', 0)
                   ->where('is_active', 1)
                   ->orderBy('category', 'ASC')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Get custom rules
     */
    public function getCustomRules()
    {
        return $this->where('is_custom', 1)
                   ->where('is_active', 1)
                   ->orderBy('category', 'ASC')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Update or create a rule
     */
    public function updateOrCreateRule($key, $value, $category = 'system', $name = null, $description = null)
    {
        $rule = $this->where('key', $key)->first();
        
        if ($rule) {
            // Update existing rule
            return $this->update($rule['id'], [
                'value' => $value,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            // Create new rule
            return $this->insert([
                'name' => $name ?? ucfirst(str_replace('_', ' ', $key)),
                'key' => $key,
                'category' => $category,
                'value' => $value,
                'description' => $description ?? 'System rule for ' . $key,
                'is_active' => 1,
                'is_custom' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    // Legacy methods for backward compatibility
    public function getCurrentRules()
    {
        $rules = $this->orderBy('last_updated', 'DESC')->first();
        return $rules ? $rules['content'] : '';
    }

    public function updateRules($content)
    {
        // Check if rules exist
        $existingRules = $this->first();
        
        if ($existingRules) {
            // Update existing rules
            return $this->update($existingRules['id'], [
                'content' => $content,
                'last_updated' => date('Y-m-d H:i:s')
            ]);
        } else {
            // Create new rules
            return $this->insert([
                'content' => $content,
                'last_updated' => date('Y-m-d H:i:s')
            ]);
        }
    }
} 
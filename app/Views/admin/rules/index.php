<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-cog"></i> System Rules & Configuration
    </h1>
    <div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRuleModal">
            <i class="fas fa-plus"></i> Add Rule
        </button>
    </div>
</div>

<!-- System Overview -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= number_format($stats['total_rules'] ?? 0) ?></h3>
                    <p class="mb-0 opacity-75">Total Rules</p>
                </div>
                <div class="text-end">
                    <i class="fas fa-cog fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="stats-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= number_format($stats['active_rules'] ?? 0) ?></h3>
                    <p class="mb-0 opacity-75">Active Rules</p>
                </div>
                <div class="text-end">
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="stats-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= number_format($stats['system_rules'] ?? 0) ?></h3>
                    <p class="mb-0 opacity-75">System Rules</p>
                </div>
                <div class="text-end">
                    <i class="fas fa-shield-alt fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="stats-card info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= number_format($stats['custom_rules'] ?? 0) ?></h3>
                    <p class="mb-0 opacity-75">Custom Rules</p>
                </div>
                <div class="text-end">
                    <i class="fas fa-user-cog fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Settings -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-gavel"></i> Auction Settings
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('admin/rules/update-auction-settings') ?>" method="post">
                    <div class="mb-3">
                        <label for="min_bid_increment" class="form-label">Minimum Bid Increment (UGX)</label>
                        <input type="number" class="form-control" id="min_bid_increment" name="min_bid_increment" 
                               value="<?= $auction_settings['min_bid_increment'] ?? 1000 ?>" min="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="max_auction_duration" class="form-label">Maximum Auction Duration (hours)</label>
                        <input type="number" class="form-control" id="max_auction_duration" name="max_auction_duration" 
                               value="<?= $auction_settings['max_auction_duration'] ?? 24 ?>" min="1" max="168" required>
                    </div>
                    <div class="mb-3">
                        <label for="auto_extend_minutes" class="form-label">Auto-extend on Last Bid (minutes)</label>
                        <input type="number" class="form-control" id="auto_extend_minutes" name="auto_extend_minutes" 
                               value="<?= $auction_settings['auto_extend_minutes'] ?? 5 ?>" min="0" max="60">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Auction Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user"></i> User Settings
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('admin/rules/update-user-settings') ?>" method="post">
                    <div class="mb-3">
                        <label for="min_tokens_to_bid" class="form-label">Minimum Tokens to Bid</label>
                        <input type="number" class="form-control" id="min_tokens_to_bid" name="min_tokens_to_bid" 
                               value="<?= $user_settings['min_tokens_to_bid'] ?? 1 ?>" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="max_concurrent_bids" class="form-label">Maximum Concurrent Bids per User</label>
                        <input type="number" class="form-control" id="max_concurrent_bids" name="max_concurrent_bids" 
                               value="<?= $user_settings['max_concurrent_bids'] ?? 5 ?>" min="1" max="20" required>
                    </div>
                    <div class="mb-3">
                        <label for="token_expiry_days" class="form-label">Token Expiry (days)</label>
                        <input type="number" class="form-control" id="token_expiry_days" name="token_expiry_days" 
                               value="<?= $user_settings['token_expiry_days'] ?? 365 ?>" min="1" max="3650">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save User Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Rules Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list"></i> System Rules
            <span class="badge bg-primary ms-2"><?= number_format($total_rules ?? 0) ?></span>
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($rules ?? [])): ?>
            <div class="text-center py-5">
                <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No rules configured</h5>
                <p class="text-muted">Add system rules to configure auction behavior.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Rule</th>
                            <th>Category</th>
                            <th>Value</th>
                            <th>Status</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rules as $rule): ?>
                            <tr>
                                <td>
                                    <div>
                                        <h6 class="mb-0"><?= esc($rule['name']) ?></h6>
                                        <small class="text-muted"><?= esc($rule['key']) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $categoryClass = 'primary';
                                    if ($rule['category'] === 'auction') $categoryClass = 'success';
                                    elseif ($rule['category'] === 'user') $categoryClass = 'info';
                                    elseif ($rule['category'] === 'system') $categoryClass = 'warning';
                                    ?>
                                    <span class="badge bg-<?= $categoryClass ?>"><?= ucfirst($rule['category']) ?></span>
                                </td>
                                <td>
                                    <?php if (is_numeric($rule['value'])): ?>
                                        <span class="fw-bold"><?= number_format($rule['value']) ?></span>
                                    <?php elseif (is_bool($rule['value'])): ?>
                                        <span class="badge bg-<?= $rule['value'] ? 'success' : 'danger' ?>">
                                            <?= $rule['value'] ? 'Yes' : 'No' ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted"><?= esc($rule['value']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = $rule['is_active'] ? 'success' : 'secondary';
                                    $statusText = $rule['is_active'] ? 'Active' : 'Inactive';
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= esc($rule['description']) ?></small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="editRule(<?= $rule['id'] ?>, '<?= esc($rule['name']) ?>', '<?= esc($rule['value']) ?>', '<?= esc($rule['description']) ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($rule['is_active']): ?>
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="toggleRule(<?= $rule['id'] ?>, false)">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="toggleRule(<?= $rule['id'] ?>, true)">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($rule['is_custom']): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteRule(<?= $rule['id'] ?>, '<?= esc($rule['name']) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Rule Modal -->
<div class="modal fade" id="addRuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Add New Rule
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/rules/add') ?>" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Rule Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="key" class="form-label">Rule Key</label>
                        <input type="text" class="form-control" id="key" name="key" required>
                        <small class="text-muted">Unique identifier for the rule (e.g., min_bid_amount)</small>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="auction">Auction</option>
                            <option value="user">User</option>
                            <option value="system">System</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="value" class="form-label">Value</label>
                        <input type="text" class="form-control" id="value" name="value" required>
                        <small class="text-muted">Can be number, text, or boolean (true/false)</small>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Rule Modal -->
<div class="modal fade" id="editRuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Edit Rule
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/rules/edit') ?>" method="post">
                <input type="hidden" id="edit_rule_id" name="rule_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Rule Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_value" class="form-label">Value</label>
                        <input type="text" class="form-control" id="edit_value" name="value" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editRule(ruleId, ruleName, ruleValue, ruleDescription) {
    document.getElementById('edit_rule_id').value = ruleId;
    document.getElementById('edit_name').value = ruleName;
    document.getElementById('edit_value').value = ruleValue;
    document.getElementById('edit_description').value = ruleDescription;
    new bootstrap.Modal(document.getElementById('editRuleModal')).show();
}

function toggleRule(ruleId, activate) {
    const action = activate ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this rule?`)) {
        window.location.href = `<?= base_url('admin/rules/toggle/') ?>${ruleId}/${action}`;
    }
}

function deleteRule(ruleId, ruleName) {
    if (confirm(`Are you sure you want to delete the rule "${ruleName}"? This action cannot be undone.`)) {
        window.location.href = `<?= base_url('admin/rules/delete/') ?>${ruleId}`;
    }
}

// Auto-generate key from name
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const key = name.toLowerCase()
        .replace(/[^a-z0-9\s]/g, '')
        .replace(/\s+/g, '_');
    document.getElementById('key').value = key;
});
</script>

<?= $this->endSection() ?> 
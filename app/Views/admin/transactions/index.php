<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-exchange-alt"></i> Transaction Management
    </h1>
    <div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
            <i class="fas fa-plus"></i> Add Transaction
        </button>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="Search by user name..." 
                       value="<?= esc($search ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="type">
                    <option value="">All Types</option>
                    <option value="deposit" <?= ($type ?? '') === 'deposit' ? 'selected' : '' ?>>Deposit</option>
                    <option value="withdrawal" <?= ($type ?? '') === 'withdrawal' ? 'selected' : '' ?>>Withdrawal</option>
                    <option value="bid" <?= ($type ?? '') === 'bid' ? 'selected' : '' ?>>Bid</option>
                    <option value="refund" <?= ($type ?? '') === 'refund' ? 'selected' : '' ?>>Refund</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="completed" <?= ($status ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="failed" <?= ($status ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="sort">
                    <option value="created_at_desc" <?= ($sort ?? '') === 'created_at_desc' ? 'selected' : '' ?>>Newest First</option>
                    <option value="created_at_asc" <?= ($sort ?? '') === 'created_at_asc' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="amount_desc" <?= ($sort ?? '') === 'amount_desc' ? 'selected' : '' ?>>Highest Amount</option>
                    <option value="amount_asc" <?= ($sort ?? '') === 'amount_asc' ? 'selected' : '' ?>>Lowest Amount</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= number_format($stats['total_transactions'] ?? 0) ?></h3>
                    <p class="mb-0 opacity-75">Total Transactions</p>
                </div>
                <div class="text-end">
                    <i class="fas fa-exchange-alt fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="stats-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= number_format($stats['completed_transactions'] ?? 0) ?></h3>
                    <p class="mb-0 opacity-75">Completed</p>
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
                    <h3 class="mb-0"><?= number_format($stats['pending_transactions'] ?? 0) ?></h3>
                    <p class="mb-0 opacity-75">Pending</p>
                </div>
                <div class="text-end">
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="stats-card info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= formatCurrency($stats['total_amount'] ?? 0) ?></h3>
                    <p class="mb-0 opacity-75">Total Amount</p>
                </div>
                <div class="text-end">
                    <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transactions Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list"></i> Transactions List
            <span class="badge bg-primary ms-2"><?= number_format($total_transactions ?? 0) ?></span>
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($transactions ?? [])): ?>
            <div class="text-center py-5">
                <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No transactions found</h5>
                <p class="text-muted">Try adjusting your search criteria or add a new transaction.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Transaction</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td>
                                    <div>
                                        <h6 class="mb-0">#<?= $transaction['id'] ?></h6>
                                        <small class="text-muted"><?= esc($transaction['reference'] ?? 'N/A') ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <?= strtoupper(substr($transaction['user_name'] ?? 'U', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?= esc($transaction['user_name'] ?? 'Unknown') ?></h6>
                                            <small class="text-muted"><?= esc($transaction['user_phone'] ?? 'N/A') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $typeClass = 'primary';
                                    $typeIcon = 'exchange-alt';
                                    if ($transaction['type'] === 'deposit') {
                                        $typeClass = 'success';
                                        $typeIcon = 'arrow-down';
                                    } elseif ($transaction['type'] === 'withdrawal') {
                                        $typeClass = 'warning';
                                        $typeIcon = 'arrow-up';
                                    } elseif ($transaction['type'] === 'bid') {
                                        $typeClass = 'info';
                                        $typeIcon = 'gavel';
                                    } elseif ($transaction['type'] === 'refund') {
                                        $typeClass = 'secondary';
                                        $typeIcon = 'undo';
                                    }
                                    ?>
                                    <span class="badge bg-<?= $typeClass ?>">
                                        <i class="fas fa-<?= $typeIcon ?>"></i> <?= ucfirst($transaction['type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $amountClass = 'text-success';
                                    if ($transaction['type'] === 'withdrawal' || $transaction['type'] === 'bid') {
                                        $amountClass = 'text-danger';
                                    }
                                    ?>
                                    <span class="fw-bold <?= $amountClass ?>">
                                        <?= formatCurrency($transaction['amount']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'warning';
                                    $statusText = 'Pending';
                                    if ($transaction['status'] === 'completed') {
                                        $statusClass = 'success';
                                        $statusText = 'Completed';
                                    } elseif ($transaction['status'] === 'failed') {
                                        $statusClass = 'danger';
                                        $statusText = 'Failed';
                                    } elseif ($transaction['status'] === 'cancelled') {
                                        $statusClass = 'secondary';
                                        $statusText = 'Cancelled';
                                    }
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M j, Y', strtotime($transaction['created_at'])) ?>
                                        <br>
                                        <span class="text-muted"><?= date('g:i A', strtotime($transaction['created_at'])) ?></span>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="viewTransaction(<?= $transaction['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($transaction['status'] === 'pending'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="approveTransaction(<?= $transaction['id'] ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="rejectTransaction(<?= $transaction['id'] ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="editTransaction(<?= $transaction['id'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
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

<!-- Pagination -->
<?php if (isset($pager) && $pager->getPageCount() > 1): ?>
    <div class="d-flex justify-content-center mt-4">
        <?= $pager->links() ?>
    </div>
<?php endif; ?>

<!-- Add Transaction Modal -->
<div class="modal fade" id="addTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Add New Transaction
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/transactions/add') ?>" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">User</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Select User</option>
                            <?php foreach ($users ?? [] as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= esc($user['name']) ?> (<?= esc($user['phone']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Transaction Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="deposit">Deposit</option>
                            <option value="withdrawal">Withdrawal</option>
                            <option value="refund">Refund</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount (UGX)</label>
                        <input type="number" class="form-control" id="amount" name="amount" min="1000" required>
                    </div>
                    <div class="mb-3">
                        <label for="reference" class="form-label">Reference</label>
                        <input type="text" class="form-control" id="reference" name="reference" placeholder="Transaction reference">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Transaction Modal -->
<div class="modal fade" id="editTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Edit Transaction
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/transactions/edit') ?>" method="post">
                <input type="hidden" id="edit_transaction_id" name="transaction_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_reference" class="form-label">Reference</label>
                        <input type="text" class="form-control" id="edit_reference" name="reference">
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewTransaction(transactionId) {
    window.location.href = `<?= base_url('admin/transactions/view/') ?>${transactionId}`;
}

function approveTransaction(transactionId) {
    if (confirm('Are you sure you want to approve this transaction?')) {
        window.location.href = `<?= base_url('admin/transactions/approve/') ?>${transactionId}`;
    }
}

function rejectTransaction(transactionId) {
    if (confirm('Are you sure you want to reject this transaction?')) {
        window.location.href = `<?= base_url('admin/transactions/reject/') ?>${transactionId}`;
    }
}

function editTransaction(transactionId) {
    // Load transaction data and show edit modal
    fetch(`<?= base_url('admin/transactions/get/') ?>${transactionId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_transaction_id').value = data.id;
            document.getElementById('edit_reference').value = data.reference || '';
            document.getElementById('edit_description').value = data.description;
            document.getElementById('edit_status').value = data.status;
            new bootstrap.Modal(document.getElementById('editTransactionModal')).show();
        });
}
</script>

<?= $this->endSection() ?> 
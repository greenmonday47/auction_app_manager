<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-users"></i> User Management
    </h1>
    <div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus"></i> Add User
        </button>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Search by name or phone..." 
                       value="<?= esc($search ?? '') ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="sort">
                    <option value="created_at_desc" <?= ($sort ?? '') === 'created_at_desc' ? 'selected' : '' ?>>Newest First</option>
                    <option value="created_at_asc" <?= ($sort ?? '') === 'created_at_asc' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="name_asc" <?= ($sort ?? '') === 'name_asc' ? 'selected' : '' ?>>Name A-Z</option>
                    <option value="name_desc" <?= ($sort ?? '') === 'name_desc' ? 'selected' : '' ?>>Name Z-A</option>
                    <option value="tokens_desc" <?= ($sort ?? '') === 'tokens_desc' ? 'selected' : '' ?>>Most Tokens</option>
                    <option value="tokens_asc" <?= ($sort ?? '') === 'tokens_asc' ? 'selected' : '' ?>>Least Tokens</option>
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

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list"></i> Users List
            <span class="badge bg-primary ms-2"><?= number_format($total_users ?? 0) ?></span>
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($users ?? [])): ?>
            <div class="text-center py-5">
                <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No users found</h5>
                <p class="text-muted">Try adjusting your search criteria or add a new user.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Phone</th>
                            <th>Tokens</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?= esc($user['name']) ?></h6>
                                            <small class="text-muted">ID: <?= $user['id'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-monospace"><?= esc($user['phone']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-success fs-6"><?= number_format($user['tokens']) ?></span>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'success';
                                    $statusText = 'Active';
                                    if ($user['tokens'] <= 0) {
                                        $statusClass = 'warning';
                                        $statusText = 'No Tokens';
                                    }
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                        <br>
                                        <span class="text-muted"><?= date('g:i A', strtotime($user['created_at'])) ?></span>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="viewUser(<?= $user['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="addTokens(<?= $user['id'] ?>, '<?= esc($user['name']) ?>')">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                onclick="editUser(<?= $user['id'] ?>, '<?= esc($user['name']) ?>', '<?= esc($user['phone']) ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteUser(<?= $user['id'] ?>, '<?= esc($user['name']) ?>')">
                                            <i class="fas fa-trash"></i>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i> Add New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/users/add') ?>" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="pin" class="form-label">PIN</label>
                        <input type="password" class="form-control" id="pin" name="pin" maxlength="4" required>
                    </div>
                    <div class="mb-3">
                        <label for="tokens" class="form-label">Initial Tokens</label>
                        <input type="number" class="form-control" id="tokens" name="tokens" value="0" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Tokens Modal -->
<div class="modal fade" id="addTokensModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Add Tokens
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/users/add-tokens') ?>" method="post">
                <input type="hidden" id="token_user_id" name="user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">User</label>
                        <input type="text" class="form-control" id="token_user_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="token_amount" class="form-label">Amount to Add</label>
                        <input type="number" class="form-control" id="token_amount" name="amount" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="token_reason" class="form-label">Reason</label>
                        <textarea class="form-control" id="token_reason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Tokens</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/users/edit') ?>" method="post">
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="edit_phone" name="phone" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function addTokens(userId, userName) {
    document.getElementById('token_user_id').value = userId;
    document.getElementById('token_user_name').value = userName;
    new bootstrap.Modal(document.getElementById('addTokensModal')).show();
}

function editUser(userId, userName, userPhone) {
    document.getElementById('edit_user_id').value = userId;
    document.getElementById('edit_name').value = userName;
    document.getElementById('edit_phone').value = userPhone;
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function deleteUser(userId, userName) {
    if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
        window.location.href = `<?= base_url('admin/users/delete/') ?>${userId}`;
    }
}

function viewUser(userId) {
    window.location.href = `<?= base_url('admin/users/view/') ?>${userId}`;
}
</script>

<?= $this->endSection() ?> 
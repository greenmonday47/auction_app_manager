<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </h1>
    <div class="text-muted">
        <i class="fas fa-clock"></i> <?= date('F j, Y g:i A') ?>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= number_format($stats['total_users']) ?></h3>
                    <p class="mb-0 opacity-75">Total Users</p>
                </div>
                <div class="text-end">
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="stats-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= number_format($stats['total_auctions']) ?></h3>
                    <p class="mb-0 opacity-75">Total Auctions</p>
                </div>
                <div class="text-end">
                    <i class="fas fa-gavel fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="stats-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= number_format($stats['live_auctions']) ?></h3>
                    <p class="mb-0 opacity-75">Live Auctions</p>
                </div>
                <div class="text-end">
                    <i class="fas fa-play-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="stats-card info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= number_format($stats['total_bids']) ?></h3>
                    <p class="mb-0 opacity-75">Total Bids</p>
                </div>
                <div class="text-end">
                    <i class="fas fa-hand-pointer fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats Row -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line"></i> Auction Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-success"><?= number_format($stats['completed_auctions']) ?></h4>
                        <p class="text-muted mb-0">Completed</p>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning"><?= number_format($stats['pending_transactions']) ?></h4>
                        <p class="text-muted mb-0">Pending Transactions</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle"></i> System Status
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Database</span>
                    <span class="badge bg-success">Connected</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>API Status</span>
                    <span class="badge bg-success">Active</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Last Updated</span>
                    <small class="text-muted"><?= date('M j, g:i A') ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row">
    <!-- Recent Users -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-users"></i> Recent Users
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_users)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-user-slash fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No users registered yet</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_users as $user): ?>
                            <div class="list-group-item border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0"><?= esc($user['name']) ?></h6>
                                        <small class="text-muted"><?= esc($user['phone']) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted"><?= date('M j', strtotime($user['created_at'])) ?></small>
                                        <br>
                                        <span class="badge bg-primary"><?= number_format($user['tokens']) ?> tokens</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Auctions -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-gavel"></i> Recent Auctions
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_auctions)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-gavel fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No auctions created yet</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_auctions as $auction): ?>
                            <div class="list-group-item border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($auction['image'])): ?>
                                            <div class="me-2">
                                                <?= getImageThumbnail($auction['image'], esc($auction['item_name']), [
                                                    'class' => 'rounded',
                                                    'style' => 'width: 40px; height: 40px; object-fit: cover;'
                                                ]) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h6 class="mb-0"><?= esc($auction['item_name']) ?></h6>
                                            <small class="text-muted"><?= formatCurrency($auction['starting_price']) ?></small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted"><?= date('M j', strtotime($auction['created_at'])) ?></small>
                                        <br>
                                        <?php
                                        $status = 'secondary';
                                        $auctionStatus = $auction['status'] ?? 'pending';
                                        if ($auctionStatus === 'live') $status = 'success';
                                        elseif ($auctionStatus === 'completed') $status = 'primary';
                                        elseif ($auctionStatus === 'cancelled') $status = 'danger';
                                        elseif ($auctionStatus === 'expired') $status = 'warning';
                                        ?>
                                        <span class="badge bg-<?= $status ?>"><?= ucfirst($auctionStatus) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Transactions -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-exchange-alt"></i> Recent Transactions
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_transactions)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-exchange-alt fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No transactions yet</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_transactions as $transaction): ?>
                            <div class="list-group-item border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0"><?= esc($transaction['user_name'] ?? 'Unknown') ?></h6>
                                        <small class="text-muted"><?= ucfirst($transaction['type']) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted"><?= date('M j', strtotime($transaction['created_at'])) ?></small>
                                        <br>
                                        <span class="badge bg-<?= $transaction['status'] === 'completed' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($transaction['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-users"></i> Manage Users
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('admin/auctions') ?>" class="btn btn-outline-success w-100">
                            <i class="fas fa-gavel"></i> Manage Auctions
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('admin/transactions') ?>" class="btn btn-outline-info w-100">
                            <i class="fas fa-exchange-alt"></i> View Transactions
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('admin/rules') ?>" class="btn btn-outline-warning w-100">
                            <i class="fas fa-cog"></i> System Rules
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?> 
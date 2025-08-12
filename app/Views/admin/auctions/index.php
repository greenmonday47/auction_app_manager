<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-gavel"></i> Auction Management
    </h1>
    <div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAuctionModal">
            <i class="fas fa-plus"></i> Create Auction
        </button>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                                 <input type="text" class="form-control" name="search" placeholder="Search by item name..." 
                        value="<?= esc($search ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="live" <?= ($status ?? '') === 'live' ? 'selected' : '' ?>>Live</option>
                    <option value="completed" <?= ($status ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="sort">
                    <option value="created_at_desc" <?= ($sort ?? '') === 'created_at_desc' ? 'selected' : '' ?>>Newest First</option>
                    <option value="created_at_asc" <?= ($sort ?? '') === 'created_at_asc' ? 'selected' : '' ?>>Oldest First</option>
                                         <option value="title_asc" <?= ($sort ?? '') === 'title_asc' ? 'selected' : '' ?>>Item Name A-Z</option>
                    <option value="starting_price_desc" <?= ($sort ?? '') === 'starting_price_desc' ? 'selected' : '' ?>>Highest Price</option>
                    <option value="starting_price_asc" <?= ($sort ?? '') === 'starting_price_asc' ? 'selected' : '' ?>>Lowest Price</option>
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
                    <h3 class="mb-0"><?= number_format($stats['total_auctions'] ?? 0) ?></h3>
                    <p class="mb-0 opacity-75">Total Auctions</p>
                </div>
                <div class="text-end">
                    <i class="fas fa-gavel fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="stats-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= number_format($stats['live_auctions'] ?? 0) ?></h3>
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
                    <h3 class="mb-0"><?= number_format($stats['completed_auctions'] ?? 0) ?></h3>
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
                    <h3 class="mb-0"><?= number_format($stats['pending_auctions'] ?? 0) ?></h3>
                    <p class="mb-0 opacity-75">Pending</p>
                </div>
                <div class="text-end">
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Auctions Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list"></i> Auctions List
            <span class="badge bg-primary ms-2"><?= number_format($total_auctions ?? 0) ?></span>
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($auctions ?? [])): ?>
            <div class="text-center py-5">
                <i class="fas fa-gavel fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No auctions found</h5>
                <p class="text-muted">Try adjusting your search criteria or create a new auction.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Auction</th>
                            <th>Starting Price</th>
                            <th>Current Bid</th>
                            <th>Status</th>
                            <th>Time Remaining</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($auctions as $auction): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($auction['image'])): ?>
                                            <div class="me-3">
                                                <?= getImageThumbnail($auction['image'], esc($auction['item_name']), [
                                                    'class' => 'rounded',
                                                    'style' => 'width: 50px; height: 50px; object-fit: cover;'
                                                ]) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h6 class="mb-0"><?= esc($auction['item_name']) ?></h6>
                                            <small class="text-muted"><?= esc($auction['description']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-success fw-bold"><?= formatCurrency($auction['starting_price']) ?></span>
                                </td>
                                <td>
                                    <?php if (isset($auction['current_bid']) && $auction['current_bid'] > 0): ?>
                                        <span class="text-primary fw-bold"><?= formatCurrency($auction['current_bid']) ?></span>
                                        <br>
                                        <small class="text-muted"><?= number_format($auction['bid_count'] ?? 0) ?> bids</small>
                                    <?php else: ?>
                                        <span class="text-muted">No bids</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'secondary';
                                    $statusText = 'Pending';
                                    $status = $auction['status'] ?? 'pending';
                                    if ($status === 'live') {
                                        $statusClass = 'success';
                                        $statusText = 'Live';
                                    } elseif ($status === 'completed') {
                                        $statusClass = 'primary';
                                        $statusText = 'Completed';
                                    } elseif ($status === 'cancelled') {
                                        $statusClass = 'danger';
                                        $statusText = 'Cancelled';
                                    } elseif ($status === 'expired') {
                                        $statusClass = 'warning';
                                        $statusText = 'Expired';
                                    }
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                </td>
                                <td>
                                    <?php
                                    $startTime = strtotime($auction['start_time']);
                                    $endTime = strtotime($auction['end_time']);
                                    $now = time();
                                    
                                    if (($auction['status'] ?? 'pending') === 'live') {
                                        $remaining = $endTime - $now;
                                        if ($remaining > 0) {
                                            $hours = floor($remaining / 3600);
                                            $minutes = floor(($remaining % 3600) / 60);
                                            echo "<span class='text-warning'>{$hours}h {$minutes}m left</span>";
                                        } else {
                                            echo "<span class='text-danger'>Expired</span>";
                                        }
                                    } else {
                                        echo "<small class='text-muted'>" . date('M j, g:i A', $startTime) . "</small>";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M j, Y', strtotime($auction['created_at'])) ?>
                                        <br>
                                        <span class="text-muted"><?= date('g:i A', strtotime($auction['created_at'])) ?></span>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="viewAuction(<?= $auction['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if (($auction['status'] ?? 'pending') === 'pending'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="startAuction(<?= $auction['id'] ?>, '<?= esc($auction['item_name']) ?>')">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (($auction['status'] ?? 'pending') === 'live'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="endAuction(<?= $auction['id'] ?>, '<?= esc($auction['item_name']) ?>')">
                                                <i class="fas fa-stop"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="editAuction(<?= $auction['id'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteAuction(<?= $auction['id'] ?>, '<?= esc($auction['item_name']) ?>')">
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

<!-- Add Auction Modal -->
<div class="modal fade" id="addAuctionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Create New Auction
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/auctions/add') ?>" method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                                                 <label for="title" class="form-label">Item Name</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="starting_price" class="form-label">Starting Price (UGX)</label>
                                <input type="number" class="form-control" id="starting_price" name="starting_price" min="1000" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image_url" class="form-label">Image URL</label>
                        <input type="url" class="form-control" id="image_url" name="image_url" 
                               placeholder="https://example.com/image.jpg" 
                               pattern="https?://.+" 
                               title="Please enter a valid URL starting with http:// or https://">
                        <div class="form-text">Enter a direct link to the item image (optional)</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="datetime-local" class="form-control" id="start_time" name="start_time" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="datetime-local" class="form-control" id="end_time" name="end_time" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Auction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Auction Modal -->
<div class="modal fade" id="editAuctionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Edit Auction
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/auctions/edit') ?>" method="post">
                <input type="hidden" id="edit_auction_id" name="auction_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                                                 <label for="edit_title" class="form-label">Item Name</label>
                                <input type="text" class="form-control" id="edit_title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_starting_price" class="form-label">Starting Price (UGX)</label>
                                <input type="number" class="form-control" id="edit_starting_price" name="starting_price" min="1000" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_image_url" class="form-label">Image URL</label>
                        <input type="url" class="form-control" id="edit_image_url" name="image_url" 
                               placeholder="https://example.com/image.jpg" 
                               pattern="https?://.+" 
                               title="Please enter a valid URL starting with http:// or https://">
                        <div class="form-text">Enter a direct link to the item image (optional)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Auction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewAuction(auctionId) {
    window.location.href = `<?= base_url('admin/auctions/view/') ?>${auctionId}`;
}

function startAuction(auctionId, title) {
    if (confirm(`Are you sure you want to start the auction "${title}"?`)) {
        window.location.href = `<?= base_url('admin/auctions/start/') ?>${auctionId}`;
    }
}

function endAuction(auctionId, title) {
    if (confirm(`Are you sure you want to end the auction "${title}"?`)) {
        window.location.href = `<?= base_url('admin/auctions/end/') ?>${auctionId}`;
    }
}

function editAuction(auctionId) {
    // Load auction data and show edit modal
    fetch(`<?= base_url('admin/auctions/get/') ?>${auctionId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_auction_id').value = data.id;
            document.getElementById('edit_title').value = data.item_name;
            document.getElementById('edit_starting_price').value = data.starting_price;
            document.getElementById('edit_description').value = data.description;
            document.getElementById('edit_image_url').value = data.image || '';
            new bootstrap.Modal(document.getElementById('editAuctionModal')).show();
        });
}

function deleteAuction(auctionId, title) {
    if (confirm(`Are you sure you want to delete the auction "${title}"? This action cannot be undone.`)) {
        window.location.href = `<?= base_url('admin/auctions/delete/') ?>${auctionId}`;
    }
}

// Set default start time to current time + 5 minutes and end time to current time + 1 hour
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const startTime = new Date(now.getTime() + 5 * 60000); // 5 minutes from now
    const endTime = new Date(now.getTime() + 60 * 60000); // 1 hour from now
    
    document.getElementById('start_time').value = startTime.toISOString().slice(0, 16);
    document.getElementById('end_time').value = endTime.toISOString().slice(0, 16);
    
    // Add validation to ensure end time is after start time
    document.getElementById('start_time').addEventListener('change', validateTimes);
    document.getElementById('end_time').addEventListener('change', validateTimes);
});

function validateTimes() {
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    
    if (startTime && endTime) {
        const start = new Date(startTime);
        const end = new Date(endTime);
        
        if (end <= start) {
            document.getElementById('end_time').setCustomValidity('End time must be after start time');
        } else {
            document.getElementById('end_time').setCustomValidity('');
        }
    }
}
</script>

<?= $this->endSection() ?> 
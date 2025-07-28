<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Auction Details</h1>
        <a href="<?= base_url('admin/auctions') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Auctions
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Auction Details -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Auction Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if (!empty($auction['image'])): ?>
                            <div class="col-md-4 mb-3">
                                <?= getImageThumbnail($auction['image'], esc($auction['item_name']), [
                                    'class' => 'img-fluid rounded',
                                    'style' => 'max-width: 100%; height: auto;'
                                ]) ?>
                            </div>
                        <?php endif; ?>
                        <div class="col-md-<?= !empty($auction['image']) ? '8' : '12' ?>">
                            <h4><?= esc($auction['item_name']) ?></h4>
                            <p class="text-muted"><?= esc($auction['description']) ?></p>
                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <strong>Starting Price:</strong><br>
                                    <span class="text-primary h5"><?= formatCurrency($auction['starting_price']) ?></span>
                                </div>
                                <div class="col-sm-6">
                                    <strong>Current Status:</strong><br>
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
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <strong>Start Time:</strong><br>
                                    <span><?= date('M j, Y g:i A', strtotime($auction['start_time'])) ?></span>
                                </div>
                                <div class="col-sm-6">
                                    <strong>End Time:</strong><br>
                                    <span><?= date('M j, Y g:i A', strtotime($auction['end_time'])) ?></span>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-sm-6">
                                    <strong>Created:</strong><br>
                                    <span><?= date('M j, Y g:i A', strtotime($auction['created_at'])) ?></span>
                                </div>
                                <?php if ($auction['updated_at']): ?>
                                    <div class="col-sm-6">
                                        <strong>Last Updated:</strong><br>
                                        <span><?= date('M j, Y g:i A', strtotime($auction['updated_at'])) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bids Section -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Bids (<?= count($bids) ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($bids)): ?>
                        <p class="text-muted text-center py-4">No bids placed yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Bidder</th>
                                        <th>Phone</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <?php if ($winning_bid): ?>
                                            <th>Status</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bids as $index => $bid): ?>
                                        <tr class="<?= ($winning_bid && $bid['id'] === $winning_bid['id']) ? 'table-success' : '' ?>">
                                            <td><?= $index + 1 ?></td>
                                            <td><?= esc($bid['user_name']) ?></td>
                                            <td><?= esc($bid['user_phone']) ?></td>
                                            <td>
                                                <strong class="text-primary"><?= formatCurrency($bid['amount']) ?></strong>
                                            </td>
                                            <td><?= date('M j, Y g:i A', strtotime($bid['created_at'])) ?></td>
                                            <?php if ($winning_bid): ?>
                                                <td>
                                                    <?php if ($bid['id'] === $winning_bid['id']): ?>
                                                        <span class="badge bg-success">Winning Bid</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Winning Bid Info -->
            <?php if ($winning_bid): ?>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Winning Bid</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <i class="fas fa-trophy fa-3x text-success"></i>
                        </div>
                        <h6 class="text-center"><?= esc($winning_bid['user_name']) ?></h6>
                        <p class="text-center text-muted"><?= esc($winning_bid['user_phone']) ?></p>
                        <div class="text-center">
                            <span class="h4 text-success"><?= formatCurrency($winning_bid['amount']) ?></span>
                        </div>
                        <small class="text-muted d-block text-center mt-2">
                            Placed on <?= date('M j, Y g:i A', strtotime($winning_bid['created_at'])) ?>
                        </small>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Auction Statistics -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary"><?= count($bids) ?></h4>
                                <small class="text-muted">Total Bids</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">
                                <?= count($bids) > 0 ? formatCurrency(max(array_column($bids, 'amount'))) : formatCurrency(0) ?>
                            </h4>
                            <small class="text-muted">Highest Bid</small>
                        </div>
                    </div>
                    
                    <?php if (count($bids) > 0): ?>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <h6 class="text-info"><?= count(array_unique(array_column($bids, 'user_id'))) ?></h6>
                                <small class="text-muted">Unique Bidders</small>
                            </div>
                            <div class="col-6">
                                <h6 class="text-warning">
                                    <?= count($bids) > 1 ? formatCurrency(max(array_column($bids, 'amount')) - min(array_column($bids, 'amount'))) : formatCurrency(0) ?>
                                </h6>
                                <small class="text-muted">Bid Range</small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if (($auction['status'] ?? 'pending') === 'pending'): ?>
                            <button type="button" class="btn btn-success" onclick="startAuction(<?= $auction['id'] ?>, '<?= esc($auction['item_name']) ?>')">
                                <i class="fas fa-play"></i> Start Auction
                            </button>
                        <?php endif; ?>
                        
                        <?php if (($auction['status'] ?? 'pending') === 'live'): ?>
                            <button type="button" class="btn btn-warning" onclick="endAuction(<?= $auction['id'] ?>, '<?= esc($auction['item_name']) ?>')">
                                <i class="fas fa-stop"></i> End Auction
                            </button>
                        <?php endif; ?>
                        
                        <?php if (($auction['status'] ?? 'pending') === 'pending'): ?>
                            <button type="button" class="btn btn-primary" onclick="editAuction(<?= $auction['id'] ?>)">
                                <i class="fas fa-edit"></i> Edit Auction
                            </button>
                        <?php endif; ?>
                        
                        <?php if (count($bids) === 0): ?>
                            <button type="button" class="btn btn-danger" onclick="deleteAuction(<?= $auction['id'] ?>, '<?= esc($auction['item_name']) ?>')">
                                <i class="fas fa-trash"></i> Delete Auction
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Auction Modal -->
<div class="modal fade" id="editAuctionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Auction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/auctions/edit') ?>" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_auction_id" name="auction_id">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="edit_title" class="form-label">Item Name</label>
                                <input type="text" class="form-control" id="edit_title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_starting_price" class="form-label">Starting Price</label>
                                <input type="number" class="form-control" id="edit_starting_price" name="starting_price" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_image_url" class="form-label">Image URL</label>
                        <input type="url" class="form-control" id="edit_image_url" name="image_url" placeholder="https://example.com/image.jpg">
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
function startAuction(auctionId, auctionName) {
    if (confirm(`Are you sure you want to start the auction "${auctionName}"?`)) {
        window.location.href = `<?= base_url('admin/auctions/start') ?>/${auctionId}`;
    }
}

function endAuction(auctionId, auctionName) {
    if (confirm(`Are you sure you want to end the auction "${auctionName}"?`)) {
        window.location.href = `<?= base_url('admin/auctions/end') ?>/${auctionId}`;
    }
}

function deleteAuction(auctionId, auctionName) {
    if (confirm(`Are you sure you want to delete the auction "${auctionName}"? This action cannot be undone.`)) {
        window.location.href = `<?= base_url('admin/auctions/delete') ?>/${auctionId}`;
    }
}

function editAuction(auctionId) {
    fetch(`<?= base_url('admin/auctions/get') ?>/${auctionId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_auction_id').value = data.id;
            document.getElementById('edit_title').value = data.item_name;
            document.getElementById('edit_starting_price').value = data.starting_price;
            document.getElementById('edit_description').value = data.description;
            document.getElementById('edit_image_url').value = data.image || '';
            
            new bootstrap.Modal(document.getElementById('editAuctionModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load auction data');
        });
}
</script>

<?= $this->endSection() ?> 
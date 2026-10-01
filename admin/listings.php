<?php
require_once __DIR__ . '/../includes/functions.php';
agri_boot_admin();

$pageTitle    = 'Listings';
$pageSubtitle = 'Manage market prices';
$currentPage  = 'listings';

// Filters
$crop    = trim($_GET['crop']    ?? '');
$market  = trim($_GET['market']  ?? '');
$status  = trim($_GET['status']  ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));

$where  = " WHERE 1=1";
$params = [];

if (in_array($crop, ['Maize','Cowpea','Tomato'], true)) {
    $where .= " AND crop = ?";
    $params[] = $crop;
}
if (in_array($market, ['Kano','Kaduna','Abuja'], true)) {
    $where .= " AND market = ?";
    $params[] = $market;
}
if ($status === 'verified') { $where .= " AND is_verified = 1"; }
if ($status === 'pending')  { $where .= " AND is_verified = 0"; }

$result = paginate(
    "SELECT COUNT(*) AS c FROM agri_listings" . $where,
    $params,
    "SELECT * FROM agri_listings" . $where . " ORDER BY updated_at DESC",
    $params,
    $page
);

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    csrf_require();
    agri_query("DELETE FROM agri_listings WHERE id = ?", [(int)$_POST['delete_id']]);
    flash_set('success', 'Listing deleted.');
    header('Location: listings.php');
    exit;
}

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require __DIR__ . '/partials/topbar.php';
?>

<div class="admin-content">

    <div class="admin-page-header">
        <div>
            <h2>Listings</h2>
            <p class="agri-text-muted agri-mb-0"><?= number_format($result['total']) ?> total listings</p>
        </div>
        <div class="admin-page-actions">
            <a href="listing-add.php" class="agri-btn agri-btn-primary agri-btn-sm">+ Add Listing</a>
        </div>
    </div>

    <div class="admin-card">
        <form method="get" class="admin-filters">
            <select name="crop" class="agri-select" onchange="this.form.submit()">
                <option value="">All Crops</option>
                <?php foreach (['Maize','Cowpea','Tomato'] as $c): ?>
                    <option value="<?= $c ?>" <?= $crop === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
            </select>
            <select name="market" class="agri-select" onchange="this.form.submit()">
                <option value="">All Markets</option>
                <?php foreach (['Kano','Kaduna','Abuja'] as $m): ?>
                    <option value="<?= $m ?>" <?= $market === $m ? 'selected' : '' ?>><?= $m ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="agri-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="verified" <?= $status === 'verified' ? 'selected' : '' ?>>Verified</option>
                <option value="pending"  <?= $status === 'pending'  ? 'selected' : '' ?>>Pending</option>
            </select>
            <input type="text" class="agri-input" placeholder="Search..." data-table-search="listings-table" style="max-width:220px;">
            <?php if ($crop || $market || $status): ?>
                <a href="listings.php" class="agri-btn agri-btn-ghost agri-btn-sm">Clear</a>
            <?php endif; ?>
        </form>

        <div class="admin-card-body admin-card-body-flush">
            <?php if (empty($result['rows'])): ?>
                <div class="agri-empty">
                    <div class="agri-empty-icon">📈</div>
                    <h3>No listings found</h3>
                    <p>Add your first listing to get started.</p>
                    <a href="listing-add.php" class="agri-btn agri-btn-primary">Add Listing</a>
                </div>
            <?php else: ?>
                <table class="admin-table" id="listings-table">
                    <thead>
                        <tr>
                            <th>Crop</th>
                            <th>Market</th>
                            <th>Price</th>
                            <th>Verifications</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['rows'] as $row): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['crop'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= htmlspecialchars($row['market'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars(formatNaira($row['price']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int)$row['verification_count'] ?></td>
                                <td><?= badge_verified($row['is_verified'], (int)$row['verification_count']) ?></td>
                                <td class="agri-text-sm agri-text-muted"><?= htmlspecialchars(timeAgo($row['updated_at']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="admin-table-actions">
                                    <a href="listing-edit.php?id=<?= (int)$row['id'] ?>" class="admin-action-edit">Edit</a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this listing?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="delete_id" value="<?= (int)$row['id'] ?>">
                                        <button type="submit" class="admin-action-delete" style="cursor:pointer;border:none;background:var(--agri-red-50);color:var(--agri-red);padding:4px 10px;border-radius:4px;font-size:12px;font-weight:600;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?= render_pagination($result['page'], $result['pages'], 'listings.php?' . http_build_query(['crop'=>$crop,'market'=>$market,'status'=>$status])) ?>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
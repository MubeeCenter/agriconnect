<?php
require_once __DIR__ . '/../includes/functions.php';
agri_boot_admin();

$pageTitle    = 'Dealers';
$pageSubtitle = 'Manage agro-dealer network';
$currentPage  = 'dealers';

$state  = trim($_GET['state'] ?? '');
$status = trim($_GET['status'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));

$where  = " WHERE 1=1";
$params = [];

if (in_array($state, ['Kano','Kaduna','Abuja'], true)) {
    $where .= " AND state = ?";
    $params[] = $state;
}
if ($status === 'verified') { $where .= " AND is_verified = 1"; }
if ($status === 'pending')  { $where .= " AND is_verified = 0"; }

$result = paginate(
    "SELECT COUNT(*) AS c FROM agri_dealers" . $where,
    $params,
    "SELECT * FROM agri_dealers" . $where . " ORDER BY business_name ASC",
    $params,
    $page
);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    csrf_require();
    agri_query("DELETE FROM agri_dealers WHERE id = ?", [(int)$_POST['delete_id']]);
    flash_set('success', 'Dealer deleted.');
    header('Location: dealers.php');
    exit;
}

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require __DIR__ . '/partials/topbar.php';
?>

<div class="admin-content">

    <div class="admin-page-header">
        <div>
            <h2>Dealers</h2>
            <p class="agri-text-muted agri-mb-0"><?= number_format($result['total']) ?> total dealers</p>
        </div>
        <div class="admin-page-actions">
            <a href="dealer-add.php" class="agri-btn agri-btn-primary agri-btn-sm">+ Add Dealer</a>
        </div>
    </div>

    <div class="admin-card">
        <form method="get" class="admin-filters">
            <select name="state" class="agri-select" onchange="this.form.submit()">
                <option value="">All States</option>
                <?php foreach (['Kano','Kaduna','Abuja'] as $s): ?>
                    <option value="<?= $s ?>" <?= $state === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="agri-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="verified" <?= $status === 'verified' ? 'selected' : '' ?>>Verified</option>
                <option value="pending"  <?= $status === 'pending'  ? 'selected' : '' ?>>Unverified</option>
            </select>
            <input type="text" class="agri-input" placeholder="Search..." data-table-search="dealers-table" style="max-width:220px;">
            <?php if ($state || $status): ?>
                <a href="dealers.php" class="agri-btn agri-btn-ghost agri-btn-sm">Clear</a>
            <?php endif; ?>
        </form>

        <div class="admin-card-body admin-card-body-flush">
            <?php if (empty($result['rows'])): ?>
                <div class="agri-empty">
                    <div class="agri-empty-icon">🏪</div>
                    <h3>No dealers found</h3>
                    <a href="dealer-add.php" class="agri-btn agri-btn-primary">Add Dealer</a>
                </div>
            <?php else: ?>
                <table class="admin-table" id="dealers-table">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Location</th>
                            <th>Phone</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['rows'] as $row): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['business_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= htmlspecialchars($row['lga'] . ', ' . $row['state'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="agri-text-sm"><?= htmlspecialchars($row['products'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= $row['is_verified'] ? '<span class="agri-badge agri-badge-success">Verified</span>' : '<span class="agri-badge agri-badge-warning">Pending</span>' ?></td>
                                <td class="admin-table-actions">
                                    <a href="dealer-edit.php?id=<?= (int)$row['id'] ?>" class="admin-action-edit">Edit</a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this dealer?');">
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

        <?= render_pagination($result['page'], $result['pages'], 'dealers.php?' . http_build_query(['state'=>$state,'status'=>$status])) ?>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
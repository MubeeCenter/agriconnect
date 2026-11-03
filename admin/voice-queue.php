<?php
require_once __DIR__ . '/../includes/functions.php';
agri_boot_admin();

$pageTitle    = 'Voice Queue';
$pageSubtitle = 'Pending and completed calls';
$currentPage  = 'voice-queue';

$status = trim($_GET['status'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));

$where  = " WHERE 1=1";
$params = [];

if (in_array($status, ['pending','connected','completed','failed'], true)) {
    $where .= " AND status = ?";
    $params[] = $status;
}

$result = paginate(
    "SELECT COUNT(*) AS c FROM agri_voice_queue" . $where,
    $params,
    "SELECT * FROM agri_voice_queue" . $where . " ORDER BY created_at DESC",
    $params,
    $page
);

// Handle "process" action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_id'])) {
    csrf_require();
    if (function_exists('process_voice_queue')) {
        $r = process_voice_queue((int)$_POST['process_id']);
        flash_set($r['success'] ? 'success' : 'danger', $r['message']);
    }
    header('Location: voice-queue.php');
    exit;
}

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require __DIR__ . '/partials/topbar.php';
?>

<div class="admin-content">

    <div class="admin-page-header">
        <div>
            <h2>Voice Call Queue</h2>
            <p class="agri-text-muted agri-mb-0"><?= number_format($result['total']) ?> entries</p>
        </div>
    </div>

    <div class="admin-card">
        <form method="get" class="admin-filters">
            <select name="status" class="agri-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <?php foreach (['pending','connected','completed','failed'] as $s): ?>
                    <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($status): ?>
                <a href="voice-queue.php" class="agri-btn agri-btn-ghost agri-btn-sm">Clear</a>
            <?php endif; ?>
        </form>

        <div class="admin-card-body admin-card-body-flush">
            <?php if (empty($result['rows'])): ?>
                <div class="agri-empty">
                    <div class="agri-empty-icon">📞</div>
                    <h3>No voice call requests</h3>
                    <p>When farmers press "1" on a dealer detail screen, requests appear here.</p>
                </div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr><th>ID</th><th>Farmer</th><th>Dealer</th><th>Status</th><th>Requested</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['rows'] as $row): ?>
                            <tr>
                                <td>#<?= (int)$row['id'] ?></td>
                                <td><?= htmlspecialchars(shortPhone($row['farmer_phone']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars(shortPhone($row['dealer_phone']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= badge_status($row['status']) ?></td>
                                <td class="agri-text-sm agri-text-muted"><?= htmlspecialchars(timeAgo($row['created_at']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="admin-table-actions">
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Initiate this call now?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="process_id" value="<?= (int)$row['id'] ?>">
                                            <button type="submit" class="admin-action-edit" style="cursor:pointer;border:none;">Initiate</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="agri-text-xs agri-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?= render_pagination($result['page'], $result['pages'], 'voice-queue.php?' . http_build_query(['status'=>$status])) ?>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
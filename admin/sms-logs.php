<?php
require_once __DIR__ . '/../includes/functions.php';
agri_boot_admin();

$pageTitle    = 'SMS Logs';
$pageSubtitle = 'Send and receive history';
$currentPage  = 'sms-logs';

$direction = trim($_GET['direction'] ?? '');
$status    = trim($_GET['status']    ?? '');
$page      = max(1, (int)($_GET['page'] ?? 1));

$where  = " WHERE 1=1";
$params = [];

if (in_array($direction, ['inbound','outbound'], true)) {
    $where .= " AND direction = ?";
    $params[] = $direction;
}
if (in_array($status, ['sent','failed','received','pending'], true)) {
    $where .= " AND status = ?";
    $params[] = $status;
}

$result = paginate(
    "SELECT COUNT(*) AS c FROM agri_sms_logs" . $where,
    $params,
    "SELECT * FROM agri_sms_logs" . $where . " ORDER BY created_at DESC",
    $params,
    $page
);

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require __DIR__ . '/partials/topbar.php';
?>

<div class="admin-content">

    <div class="admin-page-header">
        <div>
            <h2>SMS Logs</h2>
            <p class="agri-text-muted agri-mb-0"><?= number_format($result['total']) ?> entries</p>
        </div>
    </div>

    <div class="admin-card">
        <form method="get" class="admin-filters">
            <select name="direction" class="agri-select" onchange="this.form.submit()">
                <option value="">All Directions</option>
                <option value="outbound" <?= $direction === 'outbound' ? 'selected' : '' ?>>Outbound</option>
                <option value="inbound"  <?= $direction === 'inbound'  ? 'selected' : '' ?>>Inbound</option>
            </select>
            <select name="status" class="agri-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="sent"     <?= $status === 'sent'     ? 'selected' : '' ?>>Sent</option>
                <option value="failed"   <?= $status === 'failed'   ? 'selected' : '' ?>>Failed</option>
                <option value="received" <?= $status === 'received' ? 'selected' : '' ?>>Received</option>
            </select>
            <input type="text" class="agri-input" placeholder="Search..." data-table-search="sms-table" style="max-width:220px;">
            <?php if ($direction || $status): ?>
                <a href="sms-logs.php" class="agri-btn agri-btn-ghost agri-btn-sm">Clear</a>
            <?php endif; ?>
        </form>

        <div class="admin-card-body admin-card-body-flush">
            <?php if (empty($result['rows'])): ?>
                <div class="agri-empty">
                    <div class="agri-empty-icon">💬</div>
                    <h3>No SMS logs</h3>
                </div>
            <?php else: ?>
                <table class="admin-table" id="sms-table">
                    <thead>
                        <tr><th>Phone</th><th>Dir</th><th>Message</th><th>Status</th><th>When</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['rows'] as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars(shortPhone($row['phone']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="agri-badge agri-badge-<?= $row['direction'] === 'inbound' ? 'info' : 'gray' ?>">
                                        <?= htmlspecialchars(ucfirst($row['direction']), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="agri-text-sm" style="max-width:480px;">
                                    <?= htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td><?= badge_status($row['status']) ?></td>
                                <td class="agri-text-sm agri-text-muted"><?= htmlspecialchars(timeAgo($row['created_at']), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?= render_pagination($result['page'], $result['pages'], 'sms-logs.php?' . http_build_query(['direction'=>$direction,'status'=>$status])) ?>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
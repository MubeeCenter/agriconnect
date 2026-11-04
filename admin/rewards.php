<?php
require_once __DIR__ . '/../includes/functions.php';
agri_boot_admin();

$pageTitle    = 'Rewards';
$pageSubtitle = 'Airtime & data rewards sent to farmers';
$currentPage  = 'rewards';

$type = $_GET['type'] ?? 'airtime';
if (!in_array($type, ['airtime', 'data'], true)) $type = 'airtime';

// ============================================
// HANDLE TEST ACTIONS (POST)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $action = $_POST['action'] ?? '';

    // -------- Send test airtime --------
    if ($action === 'test_airtime') {
        $phone = normalize_phone($_POST['phone'] ?? '');
        
        // THE ULTIMATE FALLBACK: Guarantee amount is always 50 if missing
        $amount = isset($_POST['amount']) && is_numeric($_POST['amount']) ? (float)$_POST['amount'] : 50.00;

        if (!is_valid_phone_ng($phone)) {
            flash_set('danger', 'Please enter a valid Nigerian phone number.');
        } else {
            $result = at_send_airtime($phone, $amount);
            if (!empty($result['success'])) {
                agri_query(
                    "INSERT INTO agri_airtime_rewards (phone, amount, currency, reason, status, at_response, sent_at) 
                     VALUES (?, ?, 'NGN', 'admin_test', 'sent', ?, NOW())", 
                    [$phone, $amount, json_encode($result['data'])]
                );
                flash_set('success', "Sent â‚¦{$amount} airtime to {$phone}.");
            } else {
                agri_query(
                    "INSERT INTO agri_airtime_rewards (phone, amount, currency, reason, status, at_response, sent_at) 
                     VALUES (?, ?, 'NGN', 'admin_test', 'failed', ?, NOW())", 
                    [$phone, $amount, json_encode($result['data'])]
                );
                flash_set('warning', 'Airtime request failed. AT returned: ' . ($result['message'] ?? 'unknown'));
            }
        }
        header('Location: rewards.php?type=airtime');
        exit;
    }

    // -------- Send test data --------
    if ($action === 'test_data') {
        $phone    = normalize_phone($_POST['phone'] ?? '');
        $quantity = (int)($_POST['quantity'] ?? 500);
        $unit     = in_array($_POST['unit'] ?? 'MB', ['MB', 'GB'], true) ? $_POST['unit'] : 'MB';

        if (!is_valid_phone_ng($phone)) {
            flash_set('danger', 'Please enter a valid Nigerian phone number.');
        } else {
            $result = reward_data_bundle($phone, 'admin_test');
            if (!empty($result['success'])) {
                flash_set('success', "Sent {$quantity} {$unit} data bundle to {$phone}.");
            } else {
                flash_set('warning', 'Data request failed. AT returned: ' . ($result['message'] ?? 'unknown'));
            }
        }
        header('Location: rewards.php?type=data');
        exit;
    }
}

// ============================================
// PAGINATION & TOTALS
// ============================================
$page = max(1, (int)($_GET['page'] ?? 1));

if ($type === 'data') {
    $result = paginate(
        "SELECT COUNT(*) AS c FROM agri_data_rewards", 
        [], 
        "SELECT * FROM agri_data_rewards ORDER BY created_at DESC", 
        [], 
        $page
    );
} else {
    $result = paginate(
        "SELECT COUNT(*) AS c FROM agri_airtime_rewards", 
        [], 
        "SELECT * FROM agri_airtime_rewards ORDER BY created_at DESC", 
        [], 
        $page
    );
}

$airtimeTotal = agri_fetch_one(
    "SELECT COALESCE(SUM(amount), 0) AS s, COUNT(*) AS c 
     FROM agri_airtime_rewards WHERE status = 'sent'"
);
$dataTotal = agri_fetch_one(
    "SELECT COALESCE(SUM(quantity), 0) AS s, COUNT(*) AS c 
     FROM agri_data_rewards WHERE status = 'sent'"
);

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require __DIR__ . '/partials/topbar.php';
?>

<div class="admin-content">

    <div class="admin-page-header">
        <div>
            <h2>Rewards</h2>
            <p class="agri-text-muted agri-mb-0">Airtime & data rewards sent to farmers</p>
        </div>
        <div class="admin-page-actions">
            <button class="agri-btn agri-btn-secondary agri-btn-sm" data-modal-open="test-airtime">
                Send Test Airtime
            </button>
            <button class="agri-btn agri-btn-secondary agri-btn-sm" data-modal-open="test-data">
                Send Test Data
            </button>
            <a href="send-price-broadcast.php?cron=1&key=<?= e(CRON_KEY) ?>"
               target="_blank"
               class="agri-btn agri-btn-accent agri-btn-sm"
               onclick="return confirm('Run the daily broadcast now?');">
                Run Broadcast Now
            </a>
        </div>
    </div>

    <!-- Totals -->
    <div class="admin-stats">
        <div class="agri-stat-card is-orange">
            <div class="agri-stat-label">Airtime Rewarded</div>
            <div class="agri-stat-value"><?= formatNaira((float)$airtimeTotal['s']) ?></div>
            <div class="agri-stat-meta"><?= number_format($airtimeTotal['c']) ?> rewards sent</div>
        </div>
        <div class="agri-stat-card">
            <div class="agri-stat-label">Data Rewarded</div>
            <div class="agri-stat-value"><?= number_format((int)$dataTotal['s']) ?> MB</div>
            <div class="agri-stat-meta"><?= number_format($dataTotal['c']) ?> bundles sent</div>
        </div>
    </div>

    <!-- Tabs + Table -->
    <div class="admin-card">
        <div class="admin-filters">
            <a href="?type=airtime" class="agri-btn agri-btn-sm <?= $type === 'airtime' ? 'agri-btn-primary' : 'agri-btn-ghost' ?>">
                Airtime Rewards
            </a>
            <a href="?type=data" class="agri-btn agri-btn-sm <?= $type === 'data' ? 'agri-btn-primary' : 'agri-btn-ghost' ?>">
                Data Rewards
            </a>
        </div>

        <div class="admin-card-body admin-card-body-flush">
            <?php if (empty($result['rows'])): ?>
                <div class="agri-empty">
                    <div class="agri-empty-icon">ðŸ’°</div>
                    <h3>No <?= e($type) ?> rewards yet</h3>
                    <p>Rewards appear here as farmers submit prices and confirm listings.</p>
                </div>
            <?php elseif ($type === 'data'): ?>
                <table class="admin-table">
                    <thead>
                        <tr><th>Phone</th><th>Quantity</th><th>Reason</th><th>Status</th><th>Sent</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['rows'] as $row): ?>
                            <tr>
                                <td><?= e(shortPhone($row['phone'])) ?></td>
                                <td><?= (int)$row['quantity'] ?> <?= e($row['unit']) ?></td>
                                <td><?= e($row['reason']) ?></td>
                                <td><?= badge_status($row['status']) ?></td>
                                <td class="agri-text-sm agri-text-muted"><?= e(timeAgo($row['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr><th>Phone</th><th>Amount</th><th>Reason</th><th>Status</th><th>Sent</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['rows'] as $row): ?>
                            <tr>
                                <td><?= e(shortPhone($row['phone'])) ?></td>
                                <td><strong><?= formatNaira((float)$row['amount']) ?></strong></td>
                                <td><?= e($row['reason']) ?></td>
                                <td><?= badge_status($row['status']) ?></td>
                                <td class="agri-text-sm agri-text-muted"><?= e(timeAgo($row['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?= render_pagination($result['page'], $result['pages'], 'rewards.php?type=' . urlencode($type)) ?>
    </div>

</div>

<!-- Send Test Airtime Modal (REMOVED data-loading-form) -->
<div class="admin-modal-backdrop" id="test-airtime">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Send Test Airtime</h3>
            <button class="admin-modal-close" data-modal-close>&times;</button>
        </div>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="test_airtime">

            <div class="admin-modal-body">
                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Phone Number</label>
                    <input type="tel" name="phone" class="agri-input" required
                           data-format-phone placeholder="08012345678">
                </div>
                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Amount (NGN)</label>
                    <input type="number" step="any" name="amount" class="agri-input" required value="50">
                </div>
                <div class="agri-alert agri-alert-info">
                    This sends real airtime via the Africa's Talking Airtime API.
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="agri-btn agri-btn-ghost" data-modal-close>Cancel</button>
                <button type="submit" class="agri-btn agri-btn-primary">Send Airtime</button>
            </div>
        </form>
    </div>
</div>

<!-- Send Test Data Modal (REMOVED data-loading-form) -->
<div class="admin-modal-backdrop" id="test-data">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Send Test Data Bundle</h3>
            <button class="admin-modal-close" data-modal-close>&times;</button>
        </div>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="test_data">

            <div class="admin-modal-body">
                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Phone Number</label>
                    <input type="tel" name="phone" class="agri-input" required
                           data-format-phone placeholder="08012345678">
                </div>
                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Quantity</label>
                    <input type="number" name="quantity" class="agri-input" required value="500">
                </div>
                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Unit</label>
                    <select name="unit" class="agri-select" required>
                        <option value="MB">MB</option>
                        <option value="GB">GB</option>
                    </select>
                </div>
                <div class="agri-alert agri-alert-info">
                    Data bundles require an approved AT Payments product. Sandbox
                    responses are logged but not delivered.
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="agri-btn agri-btn-ghost" data-modal-close>Cancel</button>
                <button type="submit" class="agri-btn agri-btn-primary">Send Data</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
<?php
require_once __DIR__ . '/../includes/functions.php';
agri_boot_admin();

$pageTitle    = 'Dashboard';
$pageSubtitle = 'Overview of AgriConnect activity';
$currentPage  = 'dashboard';

// ---------- STATS ----------
$stats = [
    'farmers'       => countFarmers(),
    'dealers'       => countDealers(),
    'verified_dealers' => countDealers(true),
    'listings'      => countListings(),
    'verified_listings' => countListings(true),
    'verifications' => countVerifications(),
    'sms_sent'      => countSmsSent(),
    'sms_received'  => countSmsReceived(),
    'voice_pending' => function_exists('voice_queue_pending_count') ? voice_queue_pending_count() : 0,
];

// Unread contacts
$row = agri_fetch_one("SELECT COUNT(*) AS c FROM agri_contacts WHERE is_read = 0");
$stats['unread_contacts'] = (int)($row['c'] ?? 0);

// ---------- RECENT ACTIVITY ----------
$recentListings = agri_fetch_all(
    "SELECT * FROM agri_listings ORDER BY updated_at DESC LIMIT 5"
);

$recentVerifications = agri_fetch_all(
    "SELECT v.*, l.crop, l.market
     FROM agri_verifications v
     LEFT JOIN agri_listings l ON l.id = v.listing_id
     ORDER BY v.verified_at DESC LIMIT 5"
);

$recentSms = agri_fetch_all(
    "SELECT * FROM agri_sms_logs ORDER BY created_at DESC LIMIT 5"
);

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require __DIR__ . '/partials/topbar.php';
?>

<div class="admin-content">

    <!-- Quick Actions -->
    <div class="admin-page-header">
        <div>
            <h2>Welcome back, <?= htmlspecialchars(auth_user()['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="agri-text-muted agri-mb-0">Here's what's happening with AgriConnect today.</p>
        </div>
        <div class="admin-page-actions">
            <a href="listing-add.php" class="agri-btn agri-btn-primary agri-btn-sm">+ Add Listing</a>
            <a href="dealer-add.php" class="agri-btn agri-btn-secondary agri-btn-sm">+ Add Dealer</a>
        </div>
    </div>

    <!-- Primary Stats -->
    <div class="admin-stats">
        <div class="agri-stat-card">
            <div class="agri-stat-label">Farmers</div>
            <div class="agri-stat-value"><?= number_format($stats['farmers']) ?></div>
            <div class="agri-stat-meta">Registered via USSD</div>
        </div>
        <div class="agri-stat-card is-orange">
            <div class="agri-stat-label">Verified Dealers</div>
            <div class="agri-stat-value"><?= number_format($stats['verified_dealers']) ?></div>
            <div class="agri-stat-meta">of <?= number_format($stats['dealers']) ?> total</div>
        </div>
        <div class="agri-stat-card">
            <div class="agri-stat-label">Verified Listings</div>
            <div class="agri-stat-value"><?= number_format($stats['verified_listings']) ?></div>
            <div class="agri-stat-meta">of <?= number_format($stats['listings']) ?> total</div>
        </div>
        <div class="agri-stat-card">
            <div class="agri-stat-label">Verifications</div>
            <div class="agri-stat-value"><?= number_format($stats['verifications']) ?></div>
            <div class="agri-stat-meta">SMS confirmations</div>
        </div>
    </div>

    <!-- Secondary Stats -->
    <div class="admin-stats">
        <div class="agri-stat-card">
            <div class="agri-stat-label">SMS Sent</div>
            <div class="agri-stat-value"><?= number_format($stats['sms_sent']) ?></div>
        </div>
        <div class="agri-stat-card">
            <div class="agri-stat-label">SMS Received</div>
            <div class="agri-stat-value"><?= number_format($stats['sms_received']) ?></div>
        </div>
        <div class="agri-stat-card is-red">
            <div class="agri-stat-label">Voice Calls Pending</div>
            <div class="agri-stat-value"><?= number_format($stats['voice_pending']) ?></div>
            <div class="agri-stat-meta">
                <a href="voice-queue.php">View queue →</a>
            </div>
        </div>
        <div class="agri-stat-card is-orange">
            <div class="agri-stat-label">Unread Messages</div>
            <div class="agri-stat-value"><?= number_format($stats['unread_contacts']) ?></div>
            <div class="agri-stat-meta">
                <a href="contacts.php">Open inbox →</a>
            </div>
        </div>
    </div>

    <!-- Recent Activity Grid -->
    <div class="agri-grid agri-grid-2" style="gap:24px;align-items:flex-start;">

        <!-- Recent Listings -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Recent Listings</h3>
                <a href="listings.php" class="agri-btn agri-btn-ghost agri-btn-sm">View All</a>
            </div>
            <div class="admin-card-body admin-card-body-flush">
                <?php if (empty($recentListings)): ?>
                    <div class="agri-empty">
                        <p class="agri-mb-0">No listings yet.</p>
                    </div>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr><th>Crop</th><th>Market</th><th>Price</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentListings as $l): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($l['crop'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td><?= htmlspecialchars($l['market'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars(formatNaira($l['price']), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= badge_verified($l['is_verified'], (int)$l['verification_count']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Verifications -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Recent Verifications</h3>
                <a href="verifications.php" class="agri-btn agri-btn-ghost agri-btn-sm">View All</a>
            </div>
            <div class="admin-card-body admin-card-body-flush">
                <?php if (empty($recentVerifications)): ?>
                    <div class="agri-empty">
                        <p class="agri-mb-0">No verifications yet.</p>
                    </div>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr><th>Farmer</th><th>Listing</th><th>When</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentVerifications as $v): ?>
                                <tr>
                                    <td><?= htmlspecialchars(shortPhone($v['phone']), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?php if (!empty($v['crop'])): ?>
                                            <?= htmlspecialchars($v['crop'] . ' · ' . $v['market'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php else: ?>
                                            <span class="agri-text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="agri-text-sm agri-text-muted"><?= htmlspecialchars(timeAgo($v['verified_at']), ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent SMS -->
        <div class="admin-card" style="grid-column:span 2;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Recent SMS Activity</h3>
                <a href="sms-logs.php" class="agri-btn agri-btn-ghost agri-btn-sm">View All</a>
            </div>
            <div class="admin-card-body admin-card-body-flush">
                <?php if (empty($recentSms)): ?>
                    <div class="agri-empty">
                        <p class="agri-mb-0">No SMS activity yet.</p>
                    </div>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr><th>Phone</th><th>Direction</th><th>Message</th><th>Status</th><th>When</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentSms as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars(shortPhone($s['phone']), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="agri-badge agri-badge-<?= $s['direction'] === 'inbound' ? 'info' : 'gray' ?>"><?= htmlspecialchars(ucfirst($s['direction']), ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td class="agri-text-sm" style="max-width:400px;">
                                        <?= htmlspecialchars(mb_strimwidth($s['message'], 0, 80, '…'), ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td><?= badge_status($s['status']) ?></td>
                                    <td class="agri-text-sm agri-text-muted"><?= htmlspecialchars(timeAgo($s['created_at']), ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/at-insights.php';
agri_boot_admin();

$pageTitle    = 'Market Health';
$pageSubtitle = 'Engagement metrics across regions, crops, and dealers';
$currentPage  = 'market-health';

$metrics = get_market_health_metrics();

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require __DIR__ . '/partials/topbar.php';
?>

<div class="admin-content">

    <div class="admin-page-header">
        <div>
            <h2>Market Health Dashboard</h2>
            <p class="agri-text-muted agri-mb-0">Last 30 days of engagement</p>
        </div>
    </div>

    <!-- Totals -->
    <div class="admin-stats">
        <div class="agri-stat-card">
            <div class="agri-stat-label">Farmers</div>
            <div class="agri-stat-value"><?= number_format($metrics['totals']['farmers']) ?></div>
        </div>
        <div class="agri-stat-card is-orange">
            <div class="agri-stat-label">Verifications (30d)</div>
            <div class="agri-stat-value"><?= number_format($metrics['totals']['verifications']) ?></div>
        </div>
        <div class="agri-stat-card">
            <div class="agri-stat-label">Voice Calls (30d)</div>
            <div class="agri-stat-value"><?= number_format($metrics['totals']['voice_calls']) ?></div>
        </div>
        <div class="agri-stat-card is-red">
            <div class="agri-stat-label">Airtime Rewarded</div>
            <div class="agri-stat-value"><?= formatNaira($metrics['totals']['airtime_rewards']) ?></div>
        </div>
    </div>

    <!-- Two-column metrics -->
    <div class="agri-grid agri-grid-2" style="gap:24px;align-items:flex-start;">

        <!-- Verifications by Region -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Verifications by Region</h3>
            </div>
            <div class="admin-card-body admin-card-body-flush">
                <?php if (empty($metrics['verifications_by_region'])): ?>
                    <div class="agri-empty"><p class="agri-mb-0">No data yet.</p></div>
                <?php else: ?>
                    <table class="admin-table">
                        <thead><tr><th>Market</th><th>Verifications</th></tr></thead>
                        <tbody>
                            <?php foreach ($metrics['verifications_by_region'] as $r): ?>
                                <tr>
                                    <td><strong><?= e($r['market'] ?? 'Unknown') ?></strong></td>
                                    <td><?= number_format($r['total']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Verifications by Crop -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Verifications by Crop</h3>
            </div>
            <div class="admin-card-body admin-card-body-flush">
                <?php if (empty($metrics['verifications_by_crop'])): ?>
                    <div class="agri-empty"><p class="agri-mb-0">No data yet.</p></div>
                <?php else: ?>
                    <table class="admin-table">
                        <thead><tr><th>Crop</th><th>Verifications</th></tr></thead>
                        <tbody>
                            <?php foreach ($metrics['verifications_by_crop'] as $r): ?>
                                <tr>
                                    <td><strong><?= e($r['crop'] ?? 'Unknown') ?></strong></td>
                                    <td><?= number_format($r['total']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Dealers -->
        <div class="admin-card" style="grid-column:span 2;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Top Dealers by Call Requests (30d)</h3>
            </div>
            <div class="admin-card-body admin-card-body-flush">
                <?php if (empty($metrics['top_dealers'])): ?>
                    <div class="agri-empty"><p class="agri-mb-0">No call requests yet.</p></div>
                <?php else: ?>
                    <table class="admin-table">
                        <thead><tr><th>Dealer</th><th>State</th><th>Call Requests</th></tr></thead>
                        <tbody>
                            <?php foreach ($metrics['top_dealers'] as $r): ?>
                                <tr>
                                    <td><strong><?= e($r['business_name']) ?></strong></td>
                                    <td><?= e($r['state']) ?></td>
                                    <td><?= number_format($r['call_requests']) ?></td>
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
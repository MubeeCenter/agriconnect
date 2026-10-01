<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';

$pageTitle       = 'Market Prices';
$pageDescription = 'Live crop prices for maize, cowpea, and tomato across Kano, Kaduna, and Abuja.';
$currentPage     = 'prices';

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

// Filters
$filterCrop   = trim($_GET['crop'] ?? '');
$filterMarket = trim($_GET['market'] ?? '');

$crops   = ['Maize', 'Cowpea', 'Tomato'];
$markets = ['Kano', 'Kaduna', 'Abuja'];

// Build query
$sql    = "SELECT * FROM agri_listings WHERE 1=1";
$params = [];

if ($filterCrop && in_array($filterCrop, $crops, true)) {
    $sql .= " AND crop = ?";
    $params[] = $filterCrop;
}
if ($filterMarket && in_array($filterMarket, $markets, true)) {
    $sql .= " AND market = ?";
    $params[] = $filterMarket;
}
$sql .= " ORDER BY updated_at DESC LIMIT 60";

$listings = agri_fetch_all($sql, $params);

$heroEyebrow  = 'Live Prices · Updated Daily';
$heroTitle    = 'Market Prices';
$heroSubtitle = 'Real numbers, verified by real farmers.';
$heroCompact  = true;
$heroActions  = [];
require __DIR__ . '/partials/hero.php';
?>

<section class="agri-section">
    <div class="agri-container">

        <!-- Filters -->
        <form method="get" class="agri-card agri-mb-6" style="padding:20px;">
            <div class="agri-flex" style="gap:16px;flex-wrap:wrap;align-items:flex-end;">
                <div class="agri-form-group" style="flex:1;min-width:180px;margin:0;">
                    <label class="agri-label">Crop</label>
                    <select name="crop" class="agri-select">
                        <option value="">All Crops</option>
                        <?php foreach ($crops as $c): ?>
                            <option value="<?= e($c) ?>" <?= $filterCrop === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="agri-form-group" style="flex:1;min-width:180px;margin:0;">
                    <label class="agri-label">Market</label>
                    <select name="market" class="agri-select">
                        <option value="">All Markets</option>
                        <?php foreach ($markets as $m): ?>
                            <option value="<?= e($m) ?>" <?= $filterMarket === $m ? 'selected' : '' ?>><?= e($m) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="agri-btn agri-btn-primary">Filter</button>
                <?php if ($filterCrop || $filterMarket): ?>
                    <a href="<?= BASE_URL ?>/prices.php" class="agri-btn agri-btn-ghost">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (empty($listings)): ?>
            <div class="agri-empty">
                <div class="agri-empty-icon">📊</div>
                <h3>No prices found</h3>
                <p>Try a different filter or check back later.</p>
            </div>
        <?php else: ?>
            <div class="agri-table-wrap">
                <table class="agri-table">
                    <thead>
                        <tr>
                            <th>Crop</th>
                            <th>Market</th>
                            <th>Price / Ton</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listings as $row): ?>
                            <tr>
                                <td><strong><?= e($row['crop']) ?></strong></td>
                                <td><?= e($row['market']) ?></td>
                                <td><strong><?= e(formatNaira($row['price'])) ?></strong></td>
                                <td><?= badge_verified($row['is_verified'], (int)$row['verification_count']) ?></td>
                                <td class="agri-text-muted agri-text-sm"><?= e(timeAgo($row['updated_at'])) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/price.php?id=<?= (int)$row['id'] ?>"
                                       class="agri-btn agri-btn-ghost agri-btn-sm">Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="agri-alert agri-alert-info agri-mt-6">
                <strong>How verification works:</strong> Prices are crowdsourced from farmers. When 3+ farmers
                confirm a price by SMS, it's marked <strong>Verified</strong>.
                <a href="<?= BASE_URL ?>/verification.php" style="color:inherit;text-decoration:underline;">Learn more →</a>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php
require_once __DIR__ . '/partials/newsletter.php';
require_once __DIR__ . '/partials/footer.php';
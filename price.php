<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/prices.php');
    exit;
}

$listing = agri_fetch_one("SELECT * FROM agri_listings WHERE id = ?", [$id]);
if (!$listing) {
    header('Location: ' . BASE_URL . '/404.php');
    exit;
}

$pageTitle       = $listing['crop'] . ' in ' . $listing['market'];
$pageDescription = 'Live market price for ' . $listing['crop'] . ' in ' . $listing['market'] . '.';
$currentPage     = 'prices';

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

// Verification history
$verifications = agri_fetch_all(
    "SELECT phone, verified_at FROM agri_verifications
     WHERE listing_id = ?
     ORDER BY verified_at DESC LIMIT 20",
    [$id]
);

// Related listings (same crop, other markets)
$related = agri_fetch_all(
    "SELECT * FROM agri_listings
     WHERE crop = ? AND id != ?
     ORDER BY updated_at DESC LIMIT 3",
    [$listing['crop'], $id]
);

$heroEyebrow  = $listing['crop'] . ' · ' . $listing['market'];
$heroTitle    = formatNaira($listing['price']) . ' per ton';
$heroSubtitle = $listing['is_verified']
    ? 'Verified by ' . (int)$listing['verification_count'] . ' farmers. Updated ' . timeAgo($listing['updated_at']) . '.'
    : 'Pending verification. Updated ' . timeAgo($listing['updated_at']) . '.';
$heroCompact  = true;
$heroActions  = [
    ['label' => '← Back to Prices', 'href' => BASE_URL . '/prices.php', 'variant' => 'secondary'],
];
require __DIR__ . '/partials/hero.php';
?>

<section class="agri-section">
    <div class="agri-container agri-container-narrow">

        <div class="agri-card agri-mb-6">
            <div class="agri-card-header">
                <h3 class="agri-card-title">Price Summary</h3>
                <?= badge_verified($listing['is_verified'], (int)$listing['verification_count']) ?>
            </div>
            <div class="agri-grid agri-grid-2 agri-mt-4">
                <div>
                    <div class="agri-uppercase agri-text-muted">Crop</div>
                    <div class="agri-text-lg agri-font-bold"><?= e($listing['crop']) ?></div>
                </div>
                <div>
                    <div class="agri-uppercase agri-text-muted">Market</div>
                    <div class="agri-text-lg agri-font-bold"><?= e($listing['market']) ?></div>
                </div>
                <div>
                    <div class="agri-uppercase agri-text-muted">Price</div>
                    <div class="agri-text-lg agri-font-bold agri-text-success"><?= e(formatNaira($listing['price'])) ?> / ton</div>
                </div>
                <div>
                    <div class="agri-uppercase agri-text-muted">Last Updated</div>
                    <div class="agri-text-lg agri-font-bold"><?= e(formatDate($listing['updated_at'])) ?></div>
                </div>
            </div>
        </div>

        <div class="agri-card agri-mb-6">
            <h3>Verification History</h3>
            <p class="agri-text-muted">
                Each verification is an SMS confirmation from a farmer physically present at the market.
            </p>

            <?php if (empty($verifications)): ?>
                <p class="agri-text-muted agri-mb-0"><em>No verifications yet.</em></p>
            <?php else: ?>
                <ul style="list-style:none;padding:0;margin:0;">
                    <?php foreach ($verifications as $v): ?>
                        <li style="padding:12px 0;border-bottom:1px solid var(--agri-border);display:flex;justify-content:space-between;align-items:center;">
                            <span><?= e(shortPhone($v['phone'])) ?> confirmed this price</span>
                            <span class="agri-text-xs agri-text-muted"><?= e(timeAgo($v['verified_at'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <?php if (!empty($related)): ?>
            <h3 class="agri-mt-8">Same Crop, Other Markets</h3>
            <div class="agri-grid agri-grid-3">
                <?php foreach ($related as $r): ?>
                    <div class="agri-card agri-card-hover">
                        <div class="agri-uppercase agri-text-muted"><?= e($r['market']) ?></div>
                        <div class="agri-stat-value" style="font-size:1.25rem;"><?= e(formatNaira($r['price'])) ?></div>
                        <p class="agri-text-xs agri-text-muted agri-mb-0"><?= e(timeAgo($r['updated_at'])) ?></p>
                        <a href="?id=<?= (int)$r['id'] ?>" class="agri-btn agri-btn-ghost agri-btn-sm agri-mt-2">View</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php';
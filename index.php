<?php
/**
 * AgriConnect — Landing Page
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';

$pageTitle       = 'AgriConnect';
$pageDescription = 'USSD-first market prices and agro-dealer network for farmers across Northern Nigeria. Dial *384*0032# from any phone.';
$currentPage     = 'index';

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

// Hero
$heroEyebrow  = 'USSD-First · No Internet Required';
$heroTitle    = 'Real Market Prices. Trusted Dealers. Any Phone.';
$heroSubtitle = 'AgriConnect connects farmers across Northern Nigeria to live crop prices and verified agro-dealers — straight from a simple USSD menu.';
$heroActions  = [
    ['label' => 'View Market Prices', 'href' => BASE_URL . '/prices.php',  'variant' => 'primary'],
    ['label' => 'Find a Dealer',      'href' => BASE_URL . '/dealers.php', 'variant' => 'secondary'],
];
require __DIR__ . '/partials/hero.php';

// Fetch a few live listings for preview
$previewListings = agri_fetch_all(
    "SELECT crop, market, price, is_verified, verification_count, updated_at
     FROM agri_listings
     WHERE is_verified = 1
     ORDER BY updated_at DESC
     LIMIT 6"
);

$previewDealers = agri_fetch_all(
    "SELECT id, business_name, lga, state, products
     FROM agri_dealers
     WHERE is_verified = 1
     ORDER BY created_at DESC
     LIMIT 3"
);
?>

<!-- ============================================
     HOW IT WORKS
     ============================================ -->
<section class="agri-section">
    <div class="agri-container">
        <div class="agri-text-center agri-mb-8">
            <div class="agri-uppercase agri-text-muted">How It Works</div>
            <h2 class="agri-mt-2">Three Steps. Zero Internet.</h2>
            <p class="agri-text-muted" style="max-width:600px;margin:8px auto 0;">
                Any farmer with any phone can access real market data in seconds.
            </p>
        </div>

        <div class="agri-grid agri-grid-3">
            <div class="agri-card agri-text-center">
                <div style="font-size:2.5rem;margin-bottom:16px;">📱</div>
                <h3>1. Dial <?= e(AT_USSD_CODE) ?></h3>
                <p class="agri-text-muted">No app. No data. Just your phone keypad.</p>
            </div>
            <div class="agri-card agri-text-center">
                <div style="font-size:2.5rem;margin-bottom:16px;">💰</div>
                <h3>2. Check Live Prices</h3>
                <p class="agri-text-muted">Real numbers verified by SMS from other farmers in your region.</p>
            </div>
            <div class="agri-card agri-text-center">
                <div style="font-size:2.5rem;margin-bottom:16px;">🤝</div>
                <h3>3. Reach a Dealer</h3>
                <p class="agri-text-muted">Get connected by SMS or a direct voice call.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     LIVE PRICE PREVIEW
     ============================================ -->
<?php if (!empty($previewListings)): ?>
<section class="agri-section-sm" style="background:var(--agri-gray-100);">
    <div class="agri-container">
        <div class="agri-flex-between agri-mb-6" style="flex-wrap:wrap;gap:16px;">
            <div>
                <div class="agri-uppercase agri-text-muted">Live Market Snapshot</div>
                <h2 class="agri-mt-2 agri-mb-0">Today's Verified Prices</h2>
            </div>
            <a href="<?= BASE_URL ?>/prices.php" class="agri-btn agri-btn-secondary">View All Prices →</a>
        </div>

        <div class="agri-grid agri-grid-3">
            <?php foreach ($previewListings as $listing): ?>
                <div class="agri-card agri-card-hover">
                    <div class="agri-flex-between agri-mb-2">
                        <div>
                            <div class="agri-uppercase agri-text-muted"><?= e($listing['market']) ?></div>
                            <h4 style="margin:4px 0 0;"><?= e($listing['crop']) ?></h4>
                        </div>
                        <?= badge_verified($listing['is_verified'], (int)$listing['verification_count']) ?>
                    </div>
                    <div class="agri-stat-value" style="font-size:1.75rem;">
                        <?= e(formatNaira($listing['price'])) ?>
                        <span class="agri-text-sm agri-text-muted" style="font-weight:500;">/ton</span>
                    </div>
                    <p class="agri-text-xs agri-text-muted agri-mb-0" style="margin-top:8px;">
                        Updated <?= e(timeAgo($listing['updated_at'])) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================
     FEATURES
     ============================================ -->
<section class="agri-section">
    <div class="agri-container">
        <div class="agri-text-center agri-mb-8">
            <div class="agri-uppercase agri-text-muted">Built For Africa</div>
            <h2 class="agri-mt-2">Powered By Real Telecom APIs</h2>
        </div>

        <div class="agri-grid agri-grid-3">
            <div class="agri-card">
                <div class="agri-badge agri-badge-success agri-mb-4">USSD API</div>
                <h3>Interactive Menus</h3>
                <p class="agri-text-muted agri-mb-0">
                    Farmers navigate simple menus without any internet connection — works on 2G, 3G, or no data at all.
                </p>
            </div>
            <div class="agri-card">
                <div class="agri-badge agri-badge-info agri-mb-4">SMS API</div>
                <h3>Crowdsourced Verification</h3>
                <p class="agri-text-muted agri-mb-0">
                    Farmers confirm prices via SMS. Three confirmations and a price is marked "Verified" for everyone.
                </p>
            </div>
            <div class="agri-card">
                <div class="agri-badge agri-badge-warning agri-mb-4">Voice API</div>
                <h3>Direct Dealer Calls</h3>
                <p class="agri-text-muted agri-mb-0">
                    One tap from USSD triggers a callback that connects farmers straight to agro-dealers.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     DEALER SPOTLIGHT
     ============================================ -->
<?php if (!empty($previewDealers)): ?>
<section class="agri-section-sm">
    <div class="agri-container">
        <div class="agri-flex-between agri-mb-6" style="flex-wrap:wrap;gap:16px;">
            <div>
                <div class="agri-uppercase agri-text-muted">Trusted Network</div>
                <h2 class="agri-mt-2 agri-mb-0">Featured Agro-Dealers</h2>
            </div>
            <a href="<?= BASE_URL ?>/dealers.php" class="agri-btn agri-btn-secondary">Browse All →</a>
        </div>

        <div class="agri-grid agri-grid-3">
            <?php foreach ($previewDealers as $dealer): ?>
                <div class="agri-card agri-card-hover">
                    <h4 style="margin-top:0;"><?= e($dealer['business_name']) ?></h4>
                    <p class="agri-text-sm agri-text-muted"><?= e($dealer['lga'] . ', ' . $dealer['state']) ?></p>
                    <p class="agri-text-sm agri-mb-4"><strong>Products:</strong> <?= e($dealer['products']) ?></p>
                    <a href="<?= BASE_URL ?>/dealer.php?id=<?= (int)$dealer['id'] ?>"
                       class="agri-btn agri-btn-ghost agri-btn-sm">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================
     STATISTICS
     ============================================ -->
<?php require __DIR__ . '/partials/statistics.php'; ?>

<!-- ============================================
     CTA
     ============================================ -->
<section class="agri-section" style="background:var(--agri-orange);color:#fff;">
    <div class="agri-container agri-container-narrow agri-text-center">
        <h2 style="color:#fff;">Ready to Check Today's Prices?</h2>
        <p style="color:rgba(255,255,255,0.95);font-size:1.125rem;">
            Dial <strong style="font-family:monospace;background:rgba(255,255,255,0.2);padding:4px 10px;border-radius:6px;"><?= e(AT_USSD_CODE) ?></strong>
            on any phone right now.
        </p>
        <a href="<?= BASE_URL ?>/prices.php" class="agri-btn agri-btn-lg"
           style="background:#fff;color:var(--agri-orange);margin-top:16px;">
            Or View Prices Online
        </a>
    </div>
</section>

<?php
require_once __DIR__ . '/partials/newsletter.php';
require_once __DIR__ . '/partials/footer.php';
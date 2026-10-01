<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/dealers.php');
    exit;
}

$dealer = agri_fetch_one("SELECT * FROM agri_dealers WHERE id = ? AND is_verified = 1", [$id]);
if (!$dealer) {
    header('Location: ' . BASE_URL . '/404.php');
    exit;
}

$pageTitle       = $dealer['business_name'];
$pageDescription = 'Contact details for ' . $dealer['business_name'] . ' in ' . $dealer['state'] . '.';
$currentPage     = 'dealers';

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$heroEyebrow  = $dealer['lga'] . ', ' . $dealer['state'];
$heroTitle    = $dealer['business_name'];
$heroSubtitle = 'Products: ' . $dealer['products'];
$heroCompact  = true;
$heroActions  = [
    ['label' => '← Back to Dealers', 'href' => BASE_URL . '/dealers.php', 'variant' => 'secondary'],
];
require __DIR__ . '/partials/hero.php';
?>

<section class="agri-section">
    <div class="agri-container agri-container-narrow">

        <div class="agri-card agri-mb-6">
            <div class="agri-card-header">
                <h3 class="agri-card-title">Contact Information</h3>
                <span class="agri-badge agri-badge-success">Verified</span>
            </div>
            <div class="agri-grid agri-grid-2 agri-mt-4">
                <div>
                    <div class="agri-uppercase agri-text-muted">Phone</div>
                    <div class="agri-text-lg agri-font-bold"><?= e($dealer['phone']) ?></div>
                </div>
                <div>
                    <div class="agri-uppercase agri-text-muted">Location</div>
                    <div class="agri-text-lg agri-font-bold"><?= e($dealer['lga'] . ', ' . $dealer['state']) ?></div>
                </div>
                <?php if (!empty($dealer['email'])): ?>
                <div>
                    <div class="agri-uppercase agri-text-muted">Email</div>
                    <div class="agri-text-lg agri-font-bold"><?= e($dealer['email']) ?></div>
                </div>
                <?php endif; ?>
                <div>
                    <div class="agri-uppercase agri-text-muted">Products</div>
                    <div class="agri-text-lg agri-font-bold"><?= e($dealer['products']) ?></div>
                </div>
            </div>
            <?php if (!empty($dealer['address'])): ?>
                <hr class="agri-mt-6">
                <div class="agri-uppercase agri-text-muted">Address</div>
                <p style="margin-top:4px;"><?= nl2br(e($dealer['address'])) ?></p>
            <?php endif; ?>
        </div>

        <div class="agri-card" style="background:var(--agri-green-50);border-color:var(--agri-green-100);">
            <h3>How to reach this dealer</h3>
            <p>
                Call the number directly, or dial <strong><?= e(AT_USSD_CODE) ?></strong> and choose
                <em>Find Agro-Dealer</em> to have AgriConnect connect you automatically.
            </p>
            <a href="tel:<?= e($dealer['phone']) ?>" class="agri-btn agri-btn-primary">
                📞 Call <?= e($dealer['phone']) ?>
            </a>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php';
<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

http_response_code(404);

$pageTitle       = 'Page Not Found';
$pageDescription = 'The page you are looking for does not exist.';
$currentPage     = '';

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';
?>

<section class="agri-section" style="padding:80px 0;">
    <div class="agri-container agri-container-narrow agri-text-center">

        <div style="font-size:6rem;font-weight:900;color:var(--agri-green-medium);line-height:1;margin-bottom:16px;">
            404
        </div>
        <h1>Page Not Found</h1>
        <p class="agri-text-muted" style="font-size:1.125rem;max-width:500px;margin:16px auto 32px;">
            The page you're looking for doesn't exist or has been moved.
        </p>

        <div class="agri-flex-center" style="gap:12px;flex-wrap:wrap;">
            <a href="<?= BASE_URL ?>/index.php" class="agri-btn agri-btn-primary agri-btn-lg">← Back Home</a>
            <a href="<?= BASE_URL ?>/prices.php" class="agri-btn agri-btn-secondary agri-btn-lg">View Prices</a>
        </div>

        <p class="agri-text-sm agri-text-muted agri-mt-8">
            Or dial <strong><?= e(AT_USSD_CODE) ?></strong> from any phone.
        </p>

    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php';
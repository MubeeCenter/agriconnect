<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';

$pageTitle       = 'How Verification Works';
$pageDescription = 'Understand how AgriConnect verifies market prices through SMS.';
$currentPage     = 'prices';

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$recent = agri_fetch_all(
    "SELECT l.crop, l.market, COUNT(v.id) AS v_count
     FROM agri_listings l
     LEFT JOIN agri_verifications v ON v.listing_id = l.id
     GROUP BY l.id
     ORDER BY v_count DESC
     LIMIT 10"
);

$heroEyebrow  = 'Trust Layer';
$heroTitle    = 'How Verification Works';
$heroSubtitle = 'Crowdsourced. SMS-based. Tamper-resistant.';
$heroCompact  = true;
$heroActions  = [];
require __DIR__ . '/partials/hero.php';
?>

<section class="agri-section">
    <div class="agri-container agri-container-narrow">

        <h2>Why Verify Prices?</h2>
        <p>
            Anyone can submit a price. But for a farmer to trust it — and to act on it — the price must be
            <strong>independently confirmed</strong> by others physically present at that market.
        </p>

        <h2 class="agri-mt-8">The Verification Process</h2>

        <ol style="padding-left:24px;">
            <li class="agri-mb-4">
                <strong>A farmer submits a price</strong> via USSD, web, or SMS. The price enters the system as <em>Pending</em>.
            </li>
            <li class="agri-mb-4">
                <strong>Others see the price</strong> when they check the same crop+market. They're prompted by SMS:
                <em>"Is this price accurate? Reply YES to confirm."</em>
            </li>
            <li class="agri-mb-4">
                <strong>Each YES counts as one verification.</strong> One phone = one vote. No duplicates.
            </li>
            <li class="agri-mb-4">
                <strong>Three YES responses = Verified.</strong> The price is now publicly marked with a green badge.
            </li>
        </ol>

        <h2 class="agri-mt-8">Current Top Verified Prices</h2>

        <?php if (empty($recent)): ?>
            <p class="agri-text-muted"><em>No verifications yet.</em></p>
        <?php else: ?>
            <div class="agri-table-wrap">
                <table class="agri-table">
                    <thead>
                        <tr><th>Crop</th><th>Market</th><th>Verifications</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $r): ?>
                            <tr>
                                <td><strong><?= e($r['crop']) ?></strong></td>
                                <td><?= e($r['market']) ?></td>
                                <td><span class="agri-badge agri-badge-success"><?= (int)$r['v_count'] ?> ✅</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <h2 class="agri-mt-8">Try It Yourself</h2>
        <p>
            Dial <strong><?= e(AT_USSD_CODE) ?></strong> and select <em>Check Market Prices</em>.
            If the price you see is correct, you'll be able to confirm it by SMS.
        </p>

    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php';
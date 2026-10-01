<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';

$pageTitle       = 'Terms of Service';
$pageDescription = 'Terms and conditions for using AgriConnect.';
$currentPage     = '';

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$heroEyebrow  = 'Legal';
$heroTitle    = 'Terms of Service';
$heroSubtitle = 'Last updated: ' . date('F j, Y');
$heroCompact  = true;
$heroActions  = [];
require __DIR__ . '/partials/hero.php';
?>

<section class="agri-section">
    <div class="agri-container agri-container-narrow">

        <h2>1. Acceptance of Terms</h2>
        <p>
            By using AgriConnect — via USSD, SMS, or web — you agree to these Terms. If you do not agree, please
            do not use the platform.
        </p>

        <h2 class="agri-mt-8">2. What AgriConnect Is</h2>
        <p>
            AgriConnect is an information platform. We provide market prices, dealer listings, and communication
            tools. We do not buy, sell, or broker agricultural products.
        </p>

        <h2 class="agri-mt-8">3. Accuracy of Information</h2>
        <p>
            Prices and dealer listings are crowdsourced and independently verified by SMS. However:
        </p>
        <ul>
            <li>We do NOT guarantee every price is accurate at all times.</li>
            <li>Prices are indicative — verify before transacting.</li>
            <li>AgriConnect is not responsible for losses resulting from reliance on information provided.</li>
        </ul>

        <h2 class="agri-mt-8">4. Acceptable Use</h2>
        <p>You agree NOT to:</p>
        <ul>
            <li>Submit false prices or spam verifications.</li>
            <li>Impersonate other users or businesses.</li>
            <li>Use the platform for illegal purposes.</li>
            <li>Attempt to interfere with the platform's operation.</li>
        </ul>

        <h2 class="agri-mt-8">5. Dealer Listings</h2>
        <p>
            Verified dealers have been reviewed by our team. However, transactions between farmers and dealers
            are solely between those parties. AgriConnect is not liable for disputes.
        </p>

        <h2 class="agri-mt-8">6. Changes to Terms</h2>
        <p>We may update these Terms at any time. Continued use constitutes acceptance of changes.</p>

        <h2 class="agri-mt-8">7. Governing Law</h2>
        <p>These Terms are governed by the laws of the Federal Republic of Nigeria.</p>

        <h2 class="agri-mt-8">8. Contact</h2>
        <p>Questions? Email <a href="mailto:<?= e(MAIL_ADMIN_EMAIL) ?>"><?= e(MAIL_ADMIN_EMAIL) ?></a>.</p>

    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php';
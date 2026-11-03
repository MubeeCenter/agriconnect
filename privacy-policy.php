<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';

$pageTitle       = 'Privacy Policy';
$pageDescription = 'How AgriConnect collects, uses, and protects your data.';
$currentPage     = '';

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$heroEyebrow  = 'Legal';
$heroTitle    = 'Privacy Policy';
$heroSubtitle = 'Last updated: ' . date('F j, Y');
$heroCompact  = true;
$heroActions  = [];
require __DIR__ . '/partials/hero.php';
?>

<section class="agri-section">
    <div class="agri-container agri-container-narrow">

        <h2>1. Information We Collect</h2>
        <p>We collect the following information when you use AgriConnect:</p>
        <ul>
            <li><strong>Phone number</strong> — used to identify you when you dial <?= e(AT_USSD_CODE) ?>, send SMS, or submit prices.</li>
            <li><strong>Submitted data</strong> — prices, dealer requests, pest reports, contact form messages.</li>
            <li><strong>Technical data</strong> — approximate session timestamps and IP addresses for security.</li>
        </ul>

        <h2 class="agri-mt-8">2. How We Use Your Information</h2>
        <ul>
            <li>To verify prices and prevent fraud</li>
            <li>To send SMS alerts you have opted into</li>
            <li>To connect you with agro-dealers you requested</li>
            <li>To respond to contact form messages</li>
            <li>To improve the platform</li>
        </ul>

        <h2 class="agri-mt-8">3. What We Do NOT Do</h2>
        <ul>
            <li>We do NOT sell your personal data</li>
            <li>We do NOT share your number with third parties for marketing</li>
            <li>We do NOT use your data for purposes other than running AgriConnect</li>
        </ul>

        <h2 class="agri-mt-8">4. SMS Communications</h2>
        <p>
            You may receive SMS from AgriConnect for verification prompts, alerts you subscribed to, or
            transaction confirmations. Reply <strong>STOP</strong> to opt out of marketing alerts at any time.
        </p>

        <h2 class="agri-mt-8">5. Data Storage & Security</h2>
        <p>
            Data is stored on secure servers in Nigeria. We use industry-standard encryption for sensitive data.
            Passwords (for admin accounts) are hashed, never stored in plain text.
        </p>

        <h2 class="agri-mt-8">6. Data Retention</h2>
        <p>
            We retain price submissions and verifications indefinitely to maintain historical market data.
            Contact form messages are retained for 12 months.
        </p>

        <h2 class="agri-mt-8">7. Your Rights</h2>
        <p>You may request access to or deletion of your data by contacting us at <?= e(MAIL_ADMIN_EMAIL) ?>.</p>

        <h2 class="agri-mt-8">8. Contact</h2>
        <p>Questions? Email <a href="mailto:<?= e(MAIL_ADMIN_EMAIL) ?>"><?= e(MAIL_ADMIN_EMAIL) ?></a>.</p>

    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php';
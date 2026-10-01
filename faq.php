<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';

$pageTitle       = 'Frequently Asked Questions';
$pageDescription = 'Answers to common questions about AgriConnect.';
$currentPage     = 'faq';

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$faqs = [
    ['q' => 'Do I need internet to use AgriConnect?',
     'a' => 'No. AgriConnect works entirely through USSD and SMS. Any phone — smart or feature — can use it without any data plan.'],
    ['q' => 'How much does it cost?',
     'a' => 'AgriConnect is completely free for farmers. Standard network charges for USSD and SMS apply on your carrier, but no fees are charged by AgriConnect.'],
    ['q' => 'How do I access AgriConnect?',
     'a' => 'Dial ' . AT_USSD_CODE . ' from any phone. You will see a menu with options to check prices, find dealers, and manage your account.'],
    ['q' => 'How are prices verified?',
     'a' => 'Every price submitted is confirmed by other farmers via SMS. When 3 or more farmers independently confirm the same price, it earns a "Verified" badge.'],
    ['q' => 'What crops does AgriConnect cover?',
     'a' => 'Currently: Maize, Cowpea, and Tomato. We plan to expand to more crops and markets in the coming months.'],
    ['q' => 'Which markets are covered?',
     'a' => 'Kano, Kaduna, and Abuja. New markets are added based on farmer demand.'],
    ['q' => 'How do I find a trusted agro-dealer?',
     'a' => 'Dial ' . AT_USSD_CODE . ', choose "Find Agro-Dealer", and select your market. You will see verified dealers with their products and prices.'],
    ['q' => 'Can I list my business as a dealer?',
     'a' => 'Yes. Use our Contact page to get in touch. We verify all dealer listings before they appear publicly.'],
    ['q' => 'Is my phone number safe?',
     'a' => 'Yes. We never sell or share your phone number. It is used only to send you price alerts you opt into and to verify prices you submit.'],
    ['q' => 'How do I stop receiving SMS alerts?',
     'a' => 'Reply STOP to any SMS from AgriConnect. You can re-enable alerts anytime by dialing ' . AT_USSD_CODE . '.'],
];

$heroEyebrow  = 'Help Center';
$heroTitle    = 'Frequently Asked Questions';
$heroSubtitle = 'Everything you need to know about AgriConnect.';
$heroCompact  = true;
$heroActions  = [];
require __DIR__ . '/partials/hero.php';
?>

<section class="agri-section">
    <div class="agri-container agri-container-narrow">
        <?php foreach ($faqs as $i => $faq): ?>
            <div class="agri-card agri-mb-4">
                <h3 style="margin-top:0;color:var(--agri-green-dark);">
                    <?= e($faq['q']) ?>
                </h3>
                <p class="agri-mb-0"><?= e($faq['a']) ?></p>
            </div>
        <?php endforeach; ?>

        <div class="agri-card agri-text-center agri-mt-8" style="background:var(--agri-green-50);border-color:var(--agri-green-100);">
            <h3>Still Have Questions?</h3>
            <p>Reach out — we reply within 24 hours.</p>
            <a href="<?= BASE_URL ?>/contact.php" class="agri-btn agri-btn-primary">Contact Us</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php';
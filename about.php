<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';

$pageTitle       = 'About';
$pageDescription = 'AgriConnect is a USSD-first agricultural marketplace built by MubeeTech on Africa\'s Talking APIs.';
$currentPage     = 'about';

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$heroEyebrow  = 'Our Story';
$heroTitle    = 'Bridging Farmers and Markets';
$heroSubtitle = 'Why we built AgriConnect — and why USSD is the only interface that works for rural Africa.';
$heroCompact  = true;
$heroActions  = [];
require __DIR__ . '/partials/hero.php';
?>

<section class="agri-section">
    <div class="agri-container agri-container-narrow">

        <h2>The Problem</h2>
        <p>
            Across Northern Nigeria, millions of smallholder farmers sell their harvest blind. They don't know the
            real market price in Kano, Kaduna, or Abuja. Middlemen exploit this information gap — farmers lose
            up to <strong>40% of potential revenue</strong> on every harvest.
        </p>
        <p>
            Meanwhile, agricultural extension services, subsidized inputs, and verified buyers are scattered,
            uncoordinated, and largely inaccessible without internet or a smartphone.
        </p>

        <h2 class="agri-mt-8">Our Solution</h2>
        <p>
            AgriConnect is a <strong>USSD-first agricultural marketplace</strong>. Any farmer with any phone — smart or dumb —
            can dial <code><?= e(AT_USSD_CODE) ?></code> and instantly:
        </p>
        <ul>
            <li>Check live market prices for maize, cowpea, and tomato</li>
            <li>See prices verified by other farmers through SMS</li>
            <li>Find verified agro-dealers in their region</li>
            <li>Request a direct callback from a dealer via the Voice API</li>
        </ul>

        <h2 class="agri-mt-8">Why USSD?</h2>
        <p>
            SMS is passive. Smartphone apps require data plans. Voice alone is expensive. But USSD:
        </p>
        <ul>
            <li>Works on 100% of phones — from ₦5,000 feature phones to flagship smartphones</li>
            <li>Requires no internet, no data plan, no app installation</li>
            <li>Is instant — no waiting for SMS round trips</li>
            <li>Costs the user nothing in Nigeria</li>
        </ul>

        <h2 class="agri-mt-8">Technology Stack</h2>
        <p>AgriConnect is built on <strong>Africa's Talking</strong> APIs:</p>

        <div class="agri-grid agri-grid-3 agri-mt-4">
            <div class="agri-card">
                <h4>USSD API</h4>
                <p class="agri-text-sm agri-text-muted agri-mb-0">Interactive menu engine powering the entire farmer experience.</p>
            </div>
            <div class="agri-card">
                <h4>SMS API</h4>
                <p class="agri-text-sm agri-text-muted agri-mb-0">Crowdsourced price verification and alerts.</p>
            </div>
            <div class="agri-card">
                <h4>Voice API</h4>
                <p class="agri-text-sm agri-text-muted agri-mb-0">Direct farmer–dealer callbacks for high-trust transactions.</p>
            </div>
        </div>

        <h2 class="agri-mt-8">Built By</h2>
        <p>
            AgriConnect is a project by <strong>MubeeTech</strong>, submitted to the Africa's Talking
            <em>Telecommunications Solutions Hackathon</em> in Kano, Nigeria.
        </p>

    </div>
</section>

<?php
require_once __DIR__ . '/partials/statistics.php';
require_once __DIR__ . '/partials/newsletter.php';
require_once __DIR__ . '/partials/footer.php';
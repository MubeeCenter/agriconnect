<?php
/**
 * Public Site — Footer
 * Closes the page, renders site footer, loads JS.
 *
 * Optional:
 *   $pageScripts  array  Additional script URLs to load before </body>
 */

if (!defined('AGRICONNECT')) {
    require_once __DIR__ . '/../includes/config.php';
}

$year = date('Y');
?>

<footer class="agri-footer">
    <div class="agri-container">
        <div class="agri-grid agri-grid-4" style="gap:40px;">

            <!-- Brand -->
            <div>
                <div class="agri-logo" style="color:#fff;margin-bottom:16px;">
                    <span class="agri-logo-mark" aria-hidden="true">AC</span>
                    <span>AgriConnect</span>
                </div>
                <p class="agri-text-sm" style="color:var(--agri-gray-400);margin-bottom:12px;">
                    USSD-first market prices and agro-dealer network for farmers across Northern Nigeria.
                </p>
                <p class="agri-text-sm" style="color:var(--agri-gray-500);margin:0;">
                    Powered by <strong style="color:var(--agri-green-bright);">MubeeTech</strong><br>
                    Built on <strong style="color:var(--agri-green-bright);">Africa's Talking</strong>
                </p>
            </div>

            <!-- Platform -->
            <div>
                <h5>Platform</h5>
                <a href="<?= BASE_URL ?>/prices.php">Market Prices</a>
                <a href="<?= BASE_URL ?>/dealers.php">Agro-Dealers</a>
                <a href="<?= BASE_URL ?>/prices.php">Crop Reports</a>
            </div>

            <!-- Company -->
            <div>
                <h5>Company</h5>
                <a href="<?= BASE_URL ?>/about.php">About</a>
                <a href="<?= BASE_URL ?>/faq.php">FAQ</a>
                <a href="<?= BASE_URL ?>/contact.php">Contact</a>
            </div>

            <!-- Get Started -->
            <div>
                <h5>Get Started</h5>
                <a href="tel:<?= htmlspecialchars(AT_USSD_CODE, ENT_QUOTES, 'UTF-8') ?>">
                    Dial <strong style="color:var(--agri-orange);">
                        <?= htmlspecialchars(AT_USSD_CODE, ENT_QUOTES, 'UTF-8') ?>
                    </strong>
                </a>
                <a href="<?= BASE_URL ?>/contact.php">Report an Issue</a>
                <a href="<?= BASE_URL ?>/admin/login.php">Admin Login</a>
            </div>

        </div>

        <div class="agri-footer-bottom">
            &copy; <?= $year ?> MubeeTech. All rights reserved. ·
            <a href="<?= BASE_URL ?>/privacy-policy.php" style="display:inline;color:var(--agri-gray-500);">Privacy</a> ·
            <a href="<?= BASE_URL ?>/terms.php" style="display:inline;color:var(--agri-gray-500);">Terms</a>
        </div>
    </div>
</footer>

<!-- Back to top -->
<button id="agri-back-to-top"
        type="button"
        aria-label="Back to top"
        style="position:fixed;right:20px;bottom:20px;width:44px;height:44px;border-radius:50%;
               background:var(--agri-green-medium);color:#fff;font-size:18px;font-weight:700;
               display:none;align-items:center;justify-content:center;border:none;
               box-shadow:var(--agri-shadow-lg);z-index:900;cursor:pointer;
               transition:background .25s, transform .25s;">
    <span aria-hidden="true">↑</span>
</button>

<style>
#agri-back-to-top:hover {
    background: var(--agri-green-dark);
    transform: translateY(-2px);
}
#agri-back-to-top:focus-visible {
    outline: 3px solid var(--agri-orange);
    outline-offset: 2px;
}
</style>

<script>
(function () {
    'use strict';
    var btn = document.getElementById('agri-back-to-top');
    if (!btn) return;

    var threshold = 400;
    var ticking = false;

    function update() {
        btn.style.display = window.scrollY > threshold ? 'flex' : 'none';
        ticking = false;
    }

    window.addEventListener('scroll', function () {
        if (!ticking) {
            window.requestAnimationFrame(update);
            ticking = true;
        }
    }, { passive: true });

    btn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();
</script>

<!-- Core scripts -->
<script src="<?= BASE_URL ?>/assets/js/main.js?v=<?= APP_VERSION ?>" defer></script>
<script src="<?= BASE_URL ?>/assets/js/validation.js?v=<?= APP_VERSION ?>" defer></script>

<?php
// Page-level optional scripts (set $pageScripts = ['url1', 'url2'] before including footer)
if (!empty($pageScripts) && is_array($pageScripts)) {
    foreach ($pageScripts as $script) {
        echo '<script src="' . htmlspecialchars($script, ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";
    }
}
?>
</body>
</html>
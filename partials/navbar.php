<?php
/**
 * Public Site — Navbar
 * Expects $currentPage for active link highlighting.
 */

if (!defined('AGRICONNECT')) {
    require_once __DIR__ . '/../includes/config.php';
}

$currentPage = $currentPage ?? '';

// Nav items: slug => [label, href]
$navItems = [
    'index'   => ['Home',           '/index.php'],
    'prices'  => ['Market Prices',  '/prices.php'],
    'dealers' => ['Agro-Dealers',   '/dealers.php'],
    'about'   => ['About',          '/about.php'],
    'faq'     => ['FAQ',            '/faq.php'],
    'contact' => ['Contact',        '/contact.php'],
];
?>
<header class="agri-navbar">
    <div class="agri-navbar-inner">
        <a href="<?= BASE_URL ?>/index.php" class="agri-logo" aria-label="AgriConnect home">
            <span class="agri-logo-mark" aria-hidden="true">AC</span>
            <span>AgriConnect</span>
        </a>

        <nav aria-label="Primary">
            <ul class="agri-nav-links">
                <?php foreach ($navItems as $slug => $item): ?>
                    <li>
                        <a href="<?= BASE_URL . $item[1] ?>"
                           class="<?= $currentPage === $slug ? 'is-active' : '' ?>">
                            <?= htmlspecialchars($item[0], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li>
                    <a href="<?= BASE_URL ?>/admin/login.php"
                       class="agri-btn agri-btn-primary agri-btn-sm agri-nav-cta">
                        Admin Login
                    </a>
                </li>
            </ul>
        </nav>

        <button class="admin-menu-toggle"
                data-nav-toggle
                aria-label="Toggle menu"
                aria-expanded="false"
                style="display:none;">
            <span aria-hidden="true">☰</span>
        </button>
    </div>
</header>

<style>
/* Mobile toggle visibility — flipped by responsive.css at ≤768px */
@media (max-width: 768px) {
    .agri-navbar .admin-menu-toggle { display: inline-flex !important; }
}
</style>
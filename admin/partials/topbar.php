<?php
/**
 * Admin Layout — Topbar
 * Renders the sticky top bar inside <main>.
 * Expects $pageTitle and optionally $pageSubtitle.
 */

if (!defined('AGRICONNECT')) {
    require_once __DIR__ . '/../../includes/config.php';
}

$pageTitle    = $pageTitle    ?? 'Admin';
$pageSubtitle = $pageSubtitle ?? '';

$admin              = function_exists('auth_user') ? auth_user() : null;
$currentUser        = $admin['name'] ?? 'Admin';
$currentUserRole    = $admin['role'] ?? 'admin';
$currentUserInitial = strtoupper(substr($currentUser, 0, 1));
?>
<header class="admin-topbar">
    <div style="display:flex;align-items:center;gap:12px;min-width:0;">
        <button class="admin-menu-toggle"
                data-admin-menu-toggle
                aria-label="Toggle sidebar"
                aria-expanded="false">
            <span aria-hidden="true">☰</span>
        </button>

        <div style="min-width:0;">
            <h1 class="admin-topbar-title">
                <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <?php if (!empty($pageSubtitle)): ?>
                <p class="admin-topbar-subtitle">
                    <?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-topbar-actions">
        <span class="agri-text-xs agri-text-muted" data-live-clock style="display:none;"
              aria-hidden="true"></span>

        <div class="admin-user-menu" tabindex="0" role="button" aria-haspopup="true" aria-expanded="false">
            <div class="admin-user-avatar" aria-hidden="true"><?= htmlspecialchars($currentUserInitial, ENT_QUOTES, 'UTF-8') ?></div>
            <div style="line-height:1.2;text-align:left;">
                <div style="font-weight:600;font-size:13px;color:var(--agri-gray-900);">
                    <?= htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div style="font-size:11px;color:var(--agri-gray-500);text-transform:capitalize;">
                    <?= htmlspecialchars($currentUserRole, ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        </div>

        <a href="<?= BASE_URL ?>/admin/logout.php"
           class="agri-btn agri-btn-ghost agri-btn-sm"
           data-confirm="Log out of AgriConnect admin?">
            Logout
        </a>
    </div>
</header>
<?php
/**
 * Admin Layout — Sidebar
 * Expects $currentPage for active-link highlighting.
 * Opens <main class="admin-main"> at the end so pages stay consistent.
 */

if (!defined('AGRICONNECT')) {
    require_once __DIR__ . '/../../includes/config.php';
}

$currentPage = $currentPage ?? 'dashboard';

// Live badge counts (safely degrade if tables missing)
$pendingVoice = function_exists('voice_queue_pending_count')
    ? (int)voice_queue_pending_count()
    : 0;

$unreadContacts = 0;
if (function_exists('agri_fetch_one')) {
    $row = @agri_fetch_one("SELECT COUNT(*) AS c FROM agri_contacts WHERE is_read = 0");
    $unreadContacts = (int)($row['c'] ?? 0);
}

// Nav structure — grouped
$navGroups = [
    'Main' => [
        'dashboard' => ['Dashboard', '📊', '/admin/dashboard.php', null],
    ],
    'Content' => [
        'listings' => ['Listings', '📈', '/admin/listings.php', null],
        'dealers'  => ['Dealers',  '🏪', '/admin/dealers.php',  null],
    ],
    'Activity' => [
        'verifications' => ['Verifications', '✅', '/admin/verifications.php', null],
        'sms-logs'      => ['SMS Logs',      '💬', '/admin/sms-logs.php',      null],
        'voice-queue'   => ['Voice Queue',   '📞', '/admin/voice-queue.php',   $pendingVoice ?: null],
    ],
    'System' => [
        'contacts' => ['Contacts', '📩', '/admin/contacts.php', $unreadContacts ?: null],
        'users'    => ['Admin Users', '👤', '/admin/users.php', null],
        'settings' => ['Settings', '⚙️', '/admin/settings.php', null],
    ],
];
?>
<aside class="admin-sidebar" id="admin-sidebar">
    <div class="admin-sidebar-brand">
        <span class="agri-logo-mark" aria-hidden="true">AC</span>
        <div class="admin-sidebar-brand-text">
            AgriConnect
            <small>Admin Console</small>
        </div>
    </div>

    <nav aria-label="Admin navigation">
        <ul class="admin-nav">
            <?php foreach ($navGroups as $groupLabel => $items): ?>
                <li class="admin-nav-section"><?= htmlspecialchars($groupLabel, ENT_QUOTES, 'UTF-8') ?></li>
                <?php foreach ($items as $slug => $item):
                    [$label, $icon, $href, $badge] = $item;
                    $isActive = ($currentPage === $slug);
                ?>
                    <li>
                        <a href="<?= BASE_URL . $href ?>"
                           class="<?= $isActive ? 'is-active' : '' ?>">
                            <span class="admin-nav-icon" aria-hidden="true"><?= $icon ?></span>
                            <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if ($badge): ?>
                                <span class="admin-nav-badge"><?= (int)$badge ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="admin-sidebar-footer">
        <a href="<?= BASE_URL ?>/index.php" style="color:var(--agri-green-200);text-decoration:none;font-size:12px;">
            ← View public site
        </a>
        <div style="margin-top:8px;font-size:11px;color:var(--agri-green-200);opacity:0.7;">
            v<?= APP_VERSION ?> · <?= date('Y') ?>
        </div>
    </div>
</aside>

<main class="admin-main">
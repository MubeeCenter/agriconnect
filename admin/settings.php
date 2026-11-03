<?php
require_once __DIR__ . '/../includes/functions.php';
agri_boot_admin();
auth_require_admin();

$pageTitle    = 'Settings';
$pageSubtitle = 'System configuration & API credentials';
$currentPage  = 'settings';

$message = '';
$type    = 'success';

// Simple key-value settings table (created on first run)
agri_query("
    CREATE TABLE IF NOT EXISTS agri_settings (
        `key` VARCHAR(100) PRIMARY KEY,
        `value` TEXT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// Load existing settings
$settingsRows = agri_fetch_all("SELECT * FROM agri_settings");
$settings = [];
foreach ($settingsRows as $r) {
    $settings[$r['key']] = $r['value'];
}

$allowedKeys = [
    'at_username', 'at_sender_id', 'at_ussd_code',
    'site_name', 'site_email', 'site_phone',
    'alert_sms_enabled', 'alert_email_enabled',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    foreach ($allowedKeys as $key) {
        if (array_key_exists($key, $_POST)) {
            $val = trim((string)$_POST[$key]);
            agri_query(
                "INSERT INTO agri_settings (`key`, `value`) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
                [$key, $val]
            );
        }
    }
    flash_set('success', 'Settings saved.');
    header('Location: settings.php');
    exit;
}

// Reload after potential save
$settingsRows = agri_fetch_all("SELECT * FROM agri_settings");
$settings = [];
foreach ($settingsRows as $r) {
    $settings[$r['key']] = $r['value'];
}

$get = function($key, $default = '') use ($settings) {
    return $settings[$key] ?? $default;
};

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require __DIR__ . '/partials/topbar.php';
?>

<div class="admin-content">

    <div class="admin-page-header">
        <div>
            <h2>Settings</h2>
            <p class="agri-text-muted agri-mb-0">Configure your AgriConnect instance</p>
        </div>
    </div>

    <div class="agri-alert agri-alert-info">
        <strong>Note:</strong> Africa's Talking API credentials are stored in <code>includes/secrets.php</code>.
        Only edit the values below that are safe to change at runtime.
    </div>

    <form method="post" data-validate-form>
        <?= csrf_field() ?>

        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Africa's Talking</h3>
            </div>
            <div class="admin-card-body">
                <div class="agri-grid agri-grid-2">
                    <div class="agri-form-group">
                        <label class="agri-label">AT Username</label>
                        <input type="text" name="at_username" class="agri-input"
                               value="<?= e($get('at_username', AT_USERNAME)) ?>">
                        <div class="agri-form-hint">Only effective if secrets.php allows override.</div>
                    </div>
                    <div class="agri-form-group">
                        <label class="agri-label">Sender ID</label>
                        <input type="text" name="at_sender_id" class="agri-input"
                               value="<?= e($get('at_sender_id', AT_SENDER_ID)) ?>">
                    </div>
                    <div class="agri-form-group">
                        <label class="agri-label">USSD Code</label>
                        <input type="text" name="at_ussd_code" class="agri-input"
                               value="<?= e($get('at_ussd_code', AT_USSD_CODE)) ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Site Information</h3>
            </div>
            <div class="admin-card-body">
                <div class="agri-grid agri-grid-2">
                    <div class="agri-form-group">
                        <label class="agri-label">Site Name</label>
                        <input type="text" name="site_name" class="agri-input"
                               value="<?= e($get('site_name', APP_NAME)) ?>">
                    </div>
                    <div class="agri-form-group">
                        <label class="agri-label">Contact Email</label>
                        <input type="email" name="site_email" class="agri-input"
                               value="<?= e($get('site_email', MAIL_ADMIN_EMAIL)) ?>">
                    </div>
                    <div class="agri-form-group">
                        <label class="agri-label">Contact Phone</label>
                        <input type="tel" name="site_phone" class="agri-input"
                               value="<?= e($get('site_phone', '')) ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Alerts</h3>
            </div>
            <div class="admin-card-body">
                <label style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                    <input type="checkbox" name="alert_sms_enabled" value="1" <?= $get('alert_sms_enabled', '1') ? 'checked' : '' ?>>
                    <span>Send SMS notifications for new voice call requests</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" name="alert_email_enabled" value="1" <?= $get('alert_email_enabled', '1') ? 'checked' : '' ?>>
                    <span>Send email notifications for new contact messages</span>
                </label>
            </div>
        </div>

        <div class="agri-flex" style="gap:12px;">
            <button type="submit" class="agri-btn agri-btn-primary">Save Settings</button>
        </div>
    </form>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
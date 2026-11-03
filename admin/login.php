<?php
/**
 * Admin Login
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

// Already logged in? Go to dashboard
if (auth_check()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $result = auth_login($_POST['username'] ?? '', $_POST['password'] ?? '');
    if ($result['success']) {
        flash_set('success', $result['message']);
        $intended = auth_pull_intended();
        header('Location: ' . ($intended ?: 'dashboard.php'));
        exit;
    }
    $error = $result['message'];
}

// Pull flash messages from a prior redirect
$flashes = flash_get_all();

// ------------------------------------------------------------
// Inline "AC" favicon — consistent with public + admin partials
// ------------------------------------------------------------
$faviconSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
  <defs>
    <linearGradient id="acGrad" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#22723B"/>
      <stop offset="100%" stop-color="#2C963F"/>
    </linearGradient>
  </defs>
  <rect width="32" height="32" rx="6" ry="6" fill="url(#acGrad)"/>
  <text x="16" y="21.5"
        font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif"
        font-size="13"
        font-weight="800"
        fill="#FFFFFF"
        text-anchor="middle"
        letter-spacing="-0.5">AC</text>
</svg>
SVG;
$faviconDataUri = 'data:image/svg+xml;base64,' . base64_encode($faviconSvg);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login — AgriConnect</title>

    <!-- Favicon + theme color -->
    <link rel="icon" type="image/svg+xml" href="<?= $faviconDataUri ?>">
    <link rel="apple-touch-icon" href="<?= $faviconDataUri ?>">
    <meta name="theme-color" content="#22723B">

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= APP_VERSION ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css?v=<?= APP_VERSION ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css?v=<?= APP_VERSION ?>">
</head>
<body class="admin-login-page">

    <div class="admin-login-card">
        <div class="admin-login-header">
            <div class="agri-logo-mark">AC</div>
            <h1>Admin Sign In</h1>
            <p>AgriConnect Dashboard</p>
        </div>

        <?php foreach ($flashes as $f): ?>
            <div class="agri-alert agri-alert-<?= htmlspecialchars($f['type'], ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($f['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endforeach; ?>

        <?php if ($error): ?>
            <div class="agri-alert agri-alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" data-validate-form data-loading-form>
            <?= csrf_field() ?>

            <div class="agri-form-group">
                <label class="agri-label agri-label-required">Username</label>
                <input type="text" name="username" class="agri-input"
                       value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       required autofocus autocomplete="username">
            </div>

            <div class="agri-form-group">
                <label class="agri-label agri-label-required">Password</label>
                <div style="position:relative;">
                    <input type="password" name="password" id="login-pass" class="agri-input"
                           required autocomplete="current-password" style="padding-right:70px;">
                    <button type="button"
                            class="agri-btn agri-btn-ghost agri-btn-sm"
                            data-toggle-password="login-pass"
                            style="position:absolute;top:50%;right:6px;transform:translateY(-50%);padding:4px 10px;">
                        Show
                    </button>
                </div>
            </div>

            <button type="submit" class="agri-btn agri-btn-primary agri-btn-block agri-btn-lg">
                Sign In
            </button>
        </form>

        <p class="agri-text-center agri-text-xs agri-text-muted agri-mt-6 agri-mb-0">
            Authorized personnel only · <a href="<?= BASE_URL ?>/index.php">Back to site</a>
        </p>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/main.js?v=<?= APP_VERSION ?>"></script>
    <script src="<?= BASE_URL ?>/assets/js/validation.js?v=<?= APP_VERSION ?>"></script>
    <script src="<?= BASE_URL ?>/assets/js/admin.js?v=<?= APP_VERSION ?>"></script>
</body>
</html>
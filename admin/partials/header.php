<?php
/**
 * Admin Layout — Header
 * Opens HTML, loads CSS, starts .admin-layout grid.
 *
 * Expected variables:
 *   $pageTitle       string  Page title (e.g. "Dashboard")
 *   $pageDescription string  Optional meta description
 */

if (!defined('AGRICONNECT')) {
    require_once __DIR__ . '/../../includes/config.php';
}

$pageTitle       = $pageTitle       ?? 'Admin';
$pageDescription = $pageDescription ?? 'AgriConnect Admin Console';

$admin              = function_exists('auth_user') ? auth_user() : null;
$fullTitle          = $pageTitle . ' — AgriConnect Admin';
$currentUser        = $admin['name'] ?? 'Admin';
$currentUserInitial = strtoupper(substr($currentUser, 0, 1));

// ------------------------------------------------------------
// Inline "AC" favicon — identical to public partials/header.php
// Rendered as a data-URI SVG so no physical asset is required.
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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex, nofollow">

    <title><?= htmlspecialchars($fullTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">

    <!-- Favicon + theme color -->
    <link rel="icon" type="image/svg+xml" href="<?= $faviconDataUri ?>">
    <link rel="apple-touch-icon" href="<?= $faviconDataUri ?>">
    <meta name="theme-color" content="#22723B">

    <!-- Stylesheets — order matters -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= APP_VERSION ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css?v=<?= APP_VERSION ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css?v=<?= APP_VERSION ?>">
</head>
<body class="admin-body">
<?php
// Global flash messages
if (function_exists('flash_get_all')) {
    $flashes = flash_get_all();
    if (!empty($flashes)) {
        echo '<div id="admin-flash-stack" style="position:fixed;top:80px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;max-width:380px;">';
        foreach ($flashes as $f) {
            $type = in_array($f['type'], ['success','danger','warning','info'], true) ? $f['type'] : 'info';
            echo '<div class="agri-alert agri-alert-' . $type . '" data-auto-dismiss="6000" style="margin:0;box-shadow:0 4px 12px rgba(20,24,29,0.12);">';
            echo htmlspecialchars($f['message'], ENT_QUOTES, 'UTF-8');
            echo '</div>';
        }
        echo '</div>';
    }
}
?>

<div class="admin-layout">
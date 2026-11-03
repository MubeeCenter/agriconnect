<?php
/**
 * Public Site — Header
 * Opens the HTML document, loads CSS, sets meta tags.
 *
 * Expected variables (set before including):
 *   $pageTitle        string  Page title (e.g. "Market Prices")
 *   $pageDescription  string  Meta description
 *   $currentPage      string  Slug for nav highlighting (e.g. "prices")
 */

if (!defined('AGRICONNECT')) {
    require_once __DIR__ . '/../includes/config.php';
}

// Defaults
$pageTitle       = $pageTitle       ?? 'AgriConnect';
$pageDescription = $pageDescription ?? 'USSD-first market prices and agro-dealer network for farmers across Northern Nigeria.';
$currentPage     = $currentPage     ?? '';

// Compose full title
$fullTitle = ($pageTitle === 'AgriConnect')
    ? 'AgriConnect — Market Prices & Agro-Dealers via USSD'
    : $pageTitle . ' — AgriConnect';

// Canonical URL
$canonicalUrl = BASE_URL . ($_SERVER['REQUEST_URI'] ?? '/');

// ------------------------------------------------------------
// Inline "AC" favicon — matches .agri-logo-mark in style.css
// Rendered as a data-URI SVG so no physical asset is needed.
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

    <title><?= htmlspecialchars($fullTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">

    <!-- Open Graph / Social -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($fullTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:site_name" content="AgriConnect">
    <!-- OG image deferred until a proper 1200x630 PNG banner is available -->

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($fullTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">

    <!-- Favicon + theme color -->
    <link rel="icon" type="image/svg+xml" href="<?= $faviconDataUri ?>">
    <link rel="apple-touch-icon" href="<?= $faviconDataUri ?>">
    <meta name="theme-color" content="#22723B">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= APP_VERSION ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css?v=<?= APP_VERSION ?>">

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "AgriConnect",
        "url": "<?= BASE_URL ?>",
        "description": "USSD-first market prices and agro-dealer network for farmers across Northern Nigeria.",
        "areaServed": "Nigeria",
        "founder": { "@type": "Organization", "name": "MubeeTech" }
    }
    </script>
</head>
<body>
<?php
// Flash messages (rendered once per page)
if (function_exists('flash_get_all')) {
    $flashes = flash_get_all();
    if (!empty($flashes)) {
        echo '<div class="agri-container agri-mt-4" style="padding-top:16px;">';
        foreach ($flashes as $f) {
            $type = in_array($f['type'], ['success','danger','warning','info'], true) ? $f['type'] : 'info';
            echo '<div class="agri-alert agri-alert-' . $type . '" data-auto-dismiss="5000">';
            echo htmlspecialchars($f['message'], ENT_QUOTES, 'UTF-8');
            echo '</div>';
        }
        echo '</div>';
    }
}
?>
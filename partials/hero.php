<?php
/**
 * Reusable Hero Section
 *
 * Expected variables (all optional — sensible defaults applied):
 *   $heroTitle     string  Main headline
 *   $heroSubtitle  string  Supporting paragraph
 *   $heroEyebrow   string  Small tag above the title (e.g. "USSD-First")
 *   $heroActions   array   [ ['label'=>'...', 'href'=>'...', 'variant'=>'primary|secondary|accent'] ]
 *   $heroCompact   bool    If true, uses tighter padding (for inner pages)
 */

if (!defined('AGRICONNECT')) {
    require_once __DIR__ . '/../includes/config.php';
}

$heroEyebrow  = $heroEyebrow  ?? 'USSD-First · No Internet Required';
$heroTitle    = $heroTitle    ?? 'Real Market Prices. Trusted Dealers. Any Phone.';
$heroSubtitle = $heroSubtitle ?? 'AgriConnect connects farmers across Northern Nigeria to live crop prices and verified agro-dealers — straight from a simple USSD menu.';
$heroCompact  = $heroCompact  ?? false;

$heroActions = $heroActions ?? [
    ['label' => 'View Market Prices', 'href' => BASE_URL . '/prices.php',  'variant' => 'primary'],
    ['label' => 'Find a Dealer',      'href' => BASE_URL . '/dealers.php', 'variant' => 'secondary'],
];

$heroClass = 'agri-hero' . ($heroCompact ? ' agri-hero-compact' : '');
?>
<section class="<?= $heroClass ?>">
    <div class="agri-container">
        <div class="agri-hero-inner">
            <?php if (!empty($heroEyebrow)): ?>
                <div class="agri-uppercase"
                     style="color:rgba(255,255,255,0.85);letter-spacing:0.12em;margin-bottom:12px;">
                    <?= htmlspecialchars($heroEyebrow, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <h1><?= htmlspecialchars($heroTitle, ENT_QUOTES, 'UTF-8') ?></h1>

            <p><?= htmlspecialchars($heroSubtitle, ENT_QUOTES, 'UTF-8') ?></p>

            <?php if (!empty($heroActions)): ?>
                <div class="agri-hero-actions">
                    <?php foreach ($heroActions as $action):
                        $variant = $action['variant'] ?? 'primary';
                        $btnClass = 'agri-btn agri-btn-' . $variant;
                    ?>
                        <a href="<?= htmlspecialchars($action['href'], ENT_QUOTES, 'UTF-8') ?>"
                           class="<?= $btnClass ?> agri-btn-lg">
                            <?= htmlspecialchars($action['label'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="agri-hero-meta"
                 style="margin-top:32px;display:flex;gap:24px;flex-wrap:wrap;font-size:14px;color:rgba(255,255,255,0.9);">
                <span>✓ Works on any phone</span>
                <span>✓ No internet needed</span>
                <span>✓ Free for farmers</span>
            </div>
        </div>
    </div>
</section>

<style>
.agri-hero-compact { padding: 48px 0; }
.agri-hero-compact h1 { font-size: 1.875rem; }
.agri-hero-compact p  { font-size: 1rem; }
</style>
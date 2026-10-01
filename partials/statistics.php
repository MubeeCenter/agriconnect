<?php
/**
 * Live Statistics Strip
 * Pulls real numbers from the DB. Cache for 5 minutes to avoid hammering.
 */

if (!defined('AGRICONNECT')) {
    require_once __DIR__ . '/../includes/config.php';
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/functions.php';
}

// Simple in-session cache (5 min)
$cacheKey = '_stats_cache';
$cacheTtl = 300;

$stats = $_SESSION[$cacheKey] ?? null;
$cachedAt = $_SESSION[$cacheKey . '_at'] ?? 0;

if (!$stats || (time() - $cachedAt) > $cacheTtl) {
    $stats = [
        'farmers'       => function_exists('countFarmers')       ? countFarmers()                    : 0,
        'dealers'       => function_exists('countDealers')       ? countDealers(true)                : 0,
        'listings'      => function_exists('countListings')      ? countListings(true)               : 0,
        'verifications' => function_exists('countVerifications') ? countVerifications()              : 0,
        'sms_sent'      => function_exists('countSmsSent')       ? countSmsSent()                    : 0,
    ];
    $_SESSION[$cacheKey] = $stats;
    $_SESSION[$cacheKey . '_at'] = time();
}

$items = [
    ['label' => 'Farmers Reached',   'value' => $stats['farmers'],       'icon' => '👨🏾‍🌾'],
    ['label' => 'Verified Dealers',  'value' => $stats['dealers'],       'icon' => '🏪'],
    ['label' => 'Live Listings',     'value' => $stats['listings'],      'icon' => '📊'],
    ['label' => 'SMS Verifications', 'value' => $stats['verifications'], 'icon' => '✅'],
];
?>
<section class="agri-section-sm" style="background:var(--agri-gray-100);">
    <div class="agri-container">
        <div class="agri-grid agri-grid-4">
            <?php foreach ($items as $item): ?>
                <div class="agri-card agri-text-center">
                    <div style="font-size:2rem;margin-bottom:8px;line-height:1;">
                        <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div class="agri-stat-value" style="font-size:2rem;">
                        <?= number_format((int)$item['value']) ?>
                    </div>
                    <div class="agri-stat-label" style="margin-top:4px;">
                        <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="agri-text-center agri-text-muted agri-text-sm" style="margin-top:24px;margin-bottom:0;">
            Live figures pulled from AgriConnect's USSD + SMS network.
        </p>
    </div>
</section>
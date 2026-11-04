<?php
/**
 * AgriConnect — Daily Price Broadcast
 */

// ============================================
// BOOTSTRAP
// ============================================
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/at-sms.php';

// ============================================
// ACCESS CONTROL
// ============================================
$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    // Web access requires a valid cron key.
    if (!defined('CRON_KEY') || CRON_KEY === '') {
        http_response_code(403);
        exit('Forbidden — CRON_KEY not configured.');
    }

    $provided = $_GET['key'] ?? '';
    if (!hash_equals(CRON_KEY, (string)$provided) || ($_GET['cron'] ?? '') !== '1') {
        http_response_code(403);
        exit('Forbidden — invalid cron key.');
    }
}

// ============================================
// ENSURE LOGS DIR
// ============================================
$logDir = LOG_DIR;
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);

// ============================================
// FETCH: opted-in farmers + verified listings
// ============================================
$rows = agri_fetch_all("
    SELECT f.phone, f.state,
           l.crop, l.market, l.price, l.verification_count, l.updated_at
    FROM agri_farmers f
    JOIN agri_listings l ON l.market = f.state
    WHERE f.sms_alerts_enabled = 1
      AND l.is_verified = 1
    ORDER BY f.phone, l.crop, l.updated_at DESC
");

if (empty($rows)) {
    log_write('broadcast', "No opted-in farmers or no verified listings. Aborting.");
    if ($isCli) {
        exit("No sends.\n");
    } else {
        if (function_exists('flash_set') && function_exists('redirect')) {
            flash_set('warning', 'Broadcast aborted: No opted-in farmers or verified listings found.');
            redirect('/admin/rewards.php');
        }
        exit("No sends.\n");
    }
}

// ============================================
// GROUP: per phone, keep latest listing per crop
// ============================================
$grouped = [];
foreach ($rows as $row) {
    $phone = $row['phone'];
    $crop  = $row['crop'];

    if (!isset($grouped[$phone])) {
        $grouped[$phone] = [
            'state' => $row['state'],
            'crops' => [],
        ];
    }

    if (!isset($grouped[$phone]['crops'][$crop])) {
        $grouped[$phone]['crops'][$crop] = $row;
    }
}

// ============================================
// SEND: build and dispatch each message
// ============================================
$sent   = 0;
$failed = 0;
$startedAt = date('Y-m-d H:i:s');

foreach ($grouped as $phone => $data) {
    $lines = [];
    foreach (array_slice($data['crops'], 0, 3, true) as $crop => $l) {
        $lines[] = "$crop: " . formatNaira($l['price']) . "/ton";
    }

    if (empty($lines)) continue;

    $msg = "AgriConnect Daily Prices (" . $data['state'] . "):\n"
         . implode("\n", $lines)
         . "\nReply STOP to unsubscribe.";

    $ok = sendSMS($phone, $msg, null, true); // bulkMode = true

    if ($ok) $sent++; else $failed++;

    // Rate-limit: 5 messages per second
    usleep(200000);
}

// ============================================
// LOG SUMMARY
// ============================================
$source  = $isCli ? 'CLI' : 'cron';
$summary = "Broadcast complete ($source) | Sent: $sent | Failed: $failed | "
         . "Farmers: " . count($grouped) . " | Started: $startedAt";
log_write('broadcast', $summary);

if ($isCli) {
    echo $summary . "\n";
} else {
    // If triggered from the browser, redirect back to dashboard with a flash message
    if (function_exists('flash_set') && function_exists('redirect')) {
        flash_set('success', $summary);
        redirect('/admin/rewards.php');
    } else {
        header('Content-Type: text/plain');
        echo $summary;
    }
}
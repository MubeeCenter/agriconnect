<?php
/**
 * AgriConnect SMS Webhook
 * Endpoint: https://biz.mubeetech.com.ng/api/sms-webhook.php
 *
 * Set this URL in Africa's Talking dashboard → SMS → Callback URL
 *
 * Commands:
 *   YES   → verify the most recent listing in farmer's region
 *   STOP  → unsubscribe from alerts
 *   HELP  → usage info
 *   *     → default nudge
 *
 * Note: The verification *endpoint* lives at api/verify-price.php.
 *       This webhook simply decides *which* listing to verify (based on
 *       the farmer's region) and then calls the shared recordVerification()
 *       function. The endpoint is for callers who already know the listing_id.
 */

ini_set('display_errors', 0);
ini_set('log_errors', 1);

$baseDir = dirname(__DIR__);
if (!is_dir($baseDir . '/logs')) @mkdir($baseDir . '/logs', 0755, true);
ini_set('error_log', $baseDir . '/logs/error.log');

require_once $baseDir . '/includes/config.php';
require_once $baseDir . '/includes/db.php';
require_once $baseDir . '/includes/functions.php';

header('Content-Type: text/plain');

$phone = $_POST['from'] ?? '';
$text  = strtoupper(trim($_POST['text'] ?? ''));

// Normalize phone (accept +234 / 234 / 0 formats)
if ($phone !== '') {
    $phone = preg_replace('/\s+/', '', $phone);
    if (strpos($phone, '+234') === 0) {
        $phone = '0' . substr($phone, 4);
    } elseif (strpos($phone, '234') === 0 && strlen($phone) > 3) {
        $phone = '0' . substr($phone, 3);
    }
}

// Log inbound SMS
@agri_query(
    "INSERT INTO agri_sms_logs (phone, message, direction, status)
     VALUES (?, ?, 'inbound', 'received')",
    [$phone, $text]
);

if (empty($phone)) {
    echo "No sender.";
    exit;
}

// ============================================
// COMMAND: YES → verify latest listing in farmer's region
// ============================================
if ($text === 'YES') {
    $listing = findLatestListingForFarmer($phone);

    if (!$listing) {
        echo "No active listing found for verification.";
        exit;
    }

    // Delegate to the shared verifier (same function the endpoint uses)
    $result = recordVerification((int)$listing['id'], $phone);

    agri_log($phone, 'sms_verify',
        "listing_id={$listing['id']} source=sms ok=" . ($result['success'] ? '1' : '0'));

    echo $result['message'];
}

// ============================================
// COMMAND: STOP → unsubscribe
// ============================================
elseif ($text === 'STOP') {
    disableFarmerAlerts($phone);
    echo "You have been unsubscribed from AgriConnect alerts. Dial " . AT_USSD_CODE . " to re-enable.";
}

// ============================================
// COMMAND: HELP
// ============================================
elseif ($text === 'HELP') {
    echo "AgriConnect: Dial " . AT_USSD_CODE . " to check prices, find dealers, or report issues. Reply STOP to unsubscribe.";
}

// ============================================
// DEFAULT
// ============================================
else {
    echo "AgriConnect: Dial " . AT_USSD_CODE . " to check market prices, find agro-dealers, or report pest issues.";
}
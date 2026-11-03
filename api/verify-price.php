<?php
/**
 * AgriConnect — Canonical Price Verification Endpoint
 * Endpoint: https://biz.mubeetech.com.ng/api/verify-price.php
 *
 * Called from:
 *   - api/sms-webhook.php   (when farmer replies YES)
 *   - api/ussd.php          (when farmer presses 3 on price screen)
 *   - price.php             (AJAX "Confirm this price" button)
 *   - Admin action          (manual verify)
 *
 * Accepts POST:
 *   listing_id  int   required
 *   phone       str   required (E.164 or Nigerian format)
 *   source      str   optional (sms | ussd | web | admin)
 *   _csrf       str   optional (required only when called from a session context)
 *
 * Returns JSON:
 *   { success, message, verification_count, is_verified }
 */

ini_set('display_errors', 0);
ini_set('log_errors', 1);

$baseDir = dirname(__DIR__);
if (!is_dir($baseDir . '/logs')) @mkdir($baseDir . '/logs', 0755, true);
ini_set('error_log', $baseDir . '/logs/error.log');

require_once $baseDir . '/includes/config.php';
require_once $baseDir . '/includes/db.php';
require_once $baseDir . '/includes/functions.php';

header('Content-Type: application/json; charset=UTF-8');

// ------------------------------------------------------------
// METHOD CHECK
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// ------------------------------------------------------------
// INPUT PARSE — supports both form and JSON bodies
// ------------------------------------------------------------
$input = $_POST;

if (empty($input)) {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) $input = $decoded;
    }
}

$listingId = (int)($input['listing_id'] ?? 0);
$phone     = trim((string)($input['phone'] ?? ''));
$source    = preg_replace('/[^a-z]/', '', strtolower((string)($input['source'] ?? 'api')));

// ------------------------------------------------------------
// NORMALIZE PHONE (accept 080..., +234..., 234...)
// ------------------------------------------------------------
if ($phone !== '') {
    $phone = preg_replace('/\s+/', '', $phone);
    if (strpos($phone, '+234') === 0) {
        $phone = '0' . substr($phone, 4);
    } elseif (strpos($phone, '234') === 0 && strlen($phone) > 3) {
        $phone = '0' . substr($phone, 3);
    }
}

// ------------------------------------------------------------
// VALIDATE
// ------------------------------------------------------------
if ($listingId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid listing_id.']);
    exit;
}

if ($phone === '' || !preg_match('/^0[789][01]\d{8}$/', $phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid phone number.']);
    exit;
}

// ------------------------------------------------------------
// LOAD LISTING
// ------------------------------------------------------------
$listing = getListingById($listingId);

if (!$listing) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Listing not found.']);
    exit;
}

// ------------------------------------------------------------
// RECORD VERIFICATION
// ------------------------------------------------------------
$result = recordVerification($listingId, $phone);

// Refresh listing for updated count
$listing = getListingById($listingId);

// Audit log
agri_log($phone, 'verify_price',
    "listing_id=$listingId source=$source ok=" . ($result['success'] ? '1' : '0'));

log_write('verify_price',
    "listing_id=$listingId | phone=$phone | source=$source | " .
    ($result['success'] ? 'ok' : 'duplicate') . " | count=" . (int)$listing['verification_count']);

// ------------------------------------------------------------
// RESPONSE
// ------------------------------------------------------------
echo json_encode([
    'success'            => $result['success'],
    'message'            => $result['message'],
    'listing_id'         => $listingId,
    'verification_count' => (int)$listing['verification_count'],
    'is_verified'        => (bool)$listing['is_verified'],
    'threshold'          => USSD_VERIFY_THRESHOLD,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
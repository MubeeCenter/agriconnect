<?php
/**
 * AgriConnect — Voice Call Initiator
 * Triggered when a farmer presses "1" on the dealer detail USSD screen,
 * OR when an admin clicks "Initiate" in the voice-queue admin page.
 *
 * Since USSD sessions must return quickly, we queue the request immediately
 * and let a background process (or admin click) actually place the call.
 *
 * For the hackathon demo, we also offer a "sync" mode via admin click.
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/at-voice.php';

header('Content-Type: application/json');

// Two modes:
//   1. Internal (from admin session)  — auth required
//   2. Public (from USSD flow)        — no auth, but rate-limited

$isAdmin = auth_check();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// CSRF required for admin; public endpoint accepts form POSTs from our own API only
if ($isAdmin && !csrf_verify()) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
    exit;
}

$queueId = (int)($_POST['queue_id'] ?? 0);

if ($queueId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid queue ID.']);
    exit;
}

// Rate limit public calls: 1 call per IP per 30 seconds
if (!$isAdmin) {
    $key = '_voice_rate_' . md5($_SERVER['REMOTE_ADDR'] ?? '');
    $last = $_SESSION[$key] ?? 0;
    if (time() - $last < 30) {
        echo json_encode(['success' => false, 'message' => 'Please wait before requesting another call.']);
        exit;
    }
    $_SESSION[$key] = time();
}

if (!function_exists('process_voice_queue')) {
    echo json_encode(['success' => false, 'message' => 'Voice module not loaded.']);
    exit;
}

$result = process_voice_queue($queueId);

echo json_encode([
    'success' => $result['success'],
    'message' => $result['message'] ?? ($result['success'] ? 'Call initiated.' : 'Call failed.'),
]);
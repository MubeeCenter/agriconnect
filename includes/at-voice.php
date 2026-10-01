<?php
/**
 * Africa's Talking — Voice Wrapper
 * Handles outbound calls + XML call flows.
 * Sandbox mode simulates calls (AT retired the Voice sandbox hostname).
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (!function_exists('agri_fetch_one')) {
    require_once __DIR__ . '/functions.php';
}

// ============================================
// XML BUILDERS
// ============================================

function voice_xml($body) {
    return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<Response>' . $body . '</Response>';
}

function voice_say($text, $voice = 'en-NG-Standard-A') {
    $text = htmlspecialchars($text, ENT_XML1, 'UTF-8');
    return '<Say voice="' . $voice . '">' . $text . '</Say>';
}

function voice_play($url) {
    $url = htmlspecialchars($url, ENT_XML1, 'UTF-8');
    return '<Play url="' . $url . '"/>';
}

function voice_dial($phone, $record = false) {
    $phone = htmlspecialchars($phone, ENT_XML1, 'UTF-8');
    $rec   = $record ? ' record="true"' : '';
    return '<Dial phoneNumbers="' . $phone . '"' . $rec . '/>';
}

function voice_get_digits($actionUrl, $numDigits = 1, $timeout = 15) {
    $actionUrl = htmlspecialchars($actionUrl, ENT_XML1, 'UTF-8');
    return '<GetDigits timeout="' . (int)$timeout . '" numDigits="' . (int)$numDigits . '" callbackUrl="' . $actionUrl . '">';
}

// ============================================
// OUTBOUND CALL
// ============================================

/**
 * Initiate an outbound voice call.
 *
 * @param string $to           Phone number in +234... format
 * @param string $from         Your AT shortcode or number
 * @param string $callbackUrl  URL that returns Voice XML
 * @return array ['success'=>bool, 'message'=>string, 'data'=>array]
 */
function at_voice_call($to, $from = null, $callbackUrl = null) {
    $username = AT_USERNAME;
    $apiKey   = AT_API_KEY;
    $from     = $from ?? AT_SENDER_ID;
    $callback = $callbackUrl ?? (BASE_URL . '/api/voice-callback.php');

    // ============================================
    // SANDBOX SIMULATION
    // ============================================
    // AT retired the Voice sandbox hostname (voice.sandbox.africastalking.com).
    // Simulate in sandbox so the demo flow doesn't crash.
    if ($username === 'sandbox') {
        log_write('voice', "SIMULATED (sandbox) | TO: $to | FROM: $from | callback: $callback");

        return [
            'success' => true,
            'message' => 'Simulated call (sandbox mode).',
            'data'    => ['simulated' => true, 'to' => $to],
        ];
    }

    // ============================================
    // PRODUCTION
    // ============================================
    $url = 'https://voice.africastalking.com/call';

    $data = [
        'username'    => $username,
        'to'          => $to,
        'from'        => $from,
        'callbackUrl' => $callback,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apiKey: ' . $apiKey,
        'Accept: application/json',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    log_write('voice',
        "LIVE | TO: $to | HTTP: $httpCode | ERR: $curlErr | $response");

    $decoded = json_decode($response, true);

    $ok = ($httpCode === 200);
    $msg = 'Call failed.';
    if ($ok) {
        $msg = 'Call initiated.';
    } elseif (!empty($decoded['errorMessage'])) {
        $msg = $decoded['errorMessage'];
    } elseif (!empty($curlErr)) {
        $msg = 'Network error: ' . $curlErr;
    }

    return [
        'success' => $ok,
        'message' => $msg,
        'data'    => $decoded,
    ];
}

// ============================================
// QUEUE MANAGEMENT
// ============================================

function queue_voice_call($farmerPhone, $dealerPhone, $listingId = null) {
    agri_query(
        "INSERT INTO agri_voice_queue (farmer_phone, dealer_phone, listing_id, status)
         VALUES (?, ?, ?, 'pending')",
        [$farmerPhone, $dealerPhone, $listingId]
    );
    return agri_last_id();
}

function process_voice_queue($queueId) {
    $entry = agri_fetch_one(
        "SELECT * FROM agri_voice_queue WHERE id = ? AND status = 'pending'",
        [(int)$queueId]
    );

    if (!$entry) {
        return [
            'success' => false,
            'message' => 'Queue entry not found or already processed.',
        ];
    }

    $callbackUrl = BASE_URL . '/api/voice-callback.php?queue_id=' . (int)$entry['id'];

    $result = at_voice_call(
        $entry['farmer_phone'],
        AT_SENDER_ID,
        $callbackUrl
    );

    if ($result['success']) {
        agri_query(
            "UPDATE agri_voice_queue SET status = 'connected' WHERE id = ?",
            [$entry['id']]
        );
    } else {
        agri_query(
            "UPDATE agri_voice_queue SET status = 'failed' WHERE id = ?",
            [$entry['id']]
        );
    }

    return $result;
}

function mark_voice_completed($queueId) {
    agri_query(
        "UPDATE agri_voice_queue SET status = 'completed', completed_at = NOW() WHERE id = ?",
        [(int)$queueId]
    );
}

function voice_queue_pending_count() {
    $row = agri_fetch_one(
        "SELECT COUNT(*) AS c FROM agri_voice_queue WHERE status = 'pending'"
    );
    return (int)($row['c'] ?? 0);
}
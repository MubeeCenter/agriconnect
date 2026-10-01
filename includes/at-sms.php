<?php
/**
 * Africa's Talking SMS Wrapper
 * Sandbox-aware. In sandbox mode we omit the 'from' field entirely,
 * because AT retired the AFRICASTKNG sender and rejects custom IDs.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Send an SMS via Africa's Talking
 * @return bool  true if accepted by AT, false otherwise
 */
function sendSMS($to, $message, $from = null) {
    $username = AT_USERNAME;
    $apiKey   = AT_API_KEY;
    $isSandbox = ($username === 'sandbox');

    // Sandbox vs live URL
    $url = $isSandbox
        ? 'https://api.sandbox.africastalking.com/version1/messaging'
        : 'https://api.africastalking.com/version1/messaging';

    $data = [
        'username' => $username,
        'to'       => $to,
        'message'  => $message,
    ];

    // ============================================
    // SENDER ID HANDLING
    // ============================================
    // Sandbox: omit 'from' entirely (AT picks a default).
    // Production: use the configured sender ID (shortcode 425 or alphanumeric).
    if (!$isSandbox) {
        $data['from'] = $from ?? AT_SENDER_ID;
    }

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

    // Parse AT response — HTTP 201 is "accepted", but body carries the verdict
    $decoded   = json_decode($response, true);
    $atMessage = $decoded['SMSMessageData']['Message'] ?? '';
    $recipients = $decoded['SMSMessageData']['Recipients'] ?? [];
    $recipientStatus = $recipients[0]['status'] ?? '';
    $msgId = $recipients[0]['messageId'] ?? null;

    // A successful send has at least one recipient with status Success/Sent,
    // OR AT returns a non-error message and no recipients (rare, but happens
    // for queued sends). Treat InvalidSenderId + similar as failure.
    $looksLikeError = stripos($atMessage, 'invalid') !== false
        || stripos($atMessage, 'error') !== false
        || stripos($atMessage, 'denied') !== false;

    $success = !$looksLikeError
        && ($recipientStatus === 'Success'
            || $recipientStatus === 'Sent'
            || (!empty($recipients) && $httpCode === 201));

    $status = $success ? 'sent' : 'failed';

    // Log to DB
    @agri_query(
        "INSERT INTO agri_sms_logs (phone, message, direction, status, at_message_id)
         VALUES (?, ?, 'outbound', ?, ?)",
        [$to, $message, $status, $msgId]
    );

    // File log
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    @file_put_contents(
        $logDir . '/sms.log',
        date('Y-m-d H:i:s')
            . " | TO: $to"
            . " | FROM: " . ($data['from'] ?? '(default)')
            . " | HTTP: $httpCode"
            . " | ERR: $curlErr"
            . " | $response\n",
        FILE_APPEND
    );

    return $success;
}
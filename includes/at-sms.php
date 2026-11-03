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
 *
 * @param string $to        Recipient phone (normalized 0XXXXXXXXXX or +234...)
 * @param string $message   SMS body
 * @param string $from      Optional sender ID (production only)
 * @param bool   $bulkMode  When true, send via bulk queue endpoint
 * @return bool  true if accepted by AT, false otherwise
 */
function sendSMS($to, $message, $from = null, $bulkMode = false) {
    $username  = AT_USERNAME;
    $apiKey    = AT_API_KEY;
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
    // Production: use configured sender ID + optional bulk mode flag.
    if (!$isSandbox) {
        $data['from'] = $from ?? AT_SENDER_ID;
        if ($bulkMode) {
            $data['bulkSMSMode'] = 1;
        }
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

    // Parse AT response
    $decoded          = json_decode($response, true);
    $atMessage        = $decoded['SMSMessageData']['Message']    ?? '';
    $recipients       = $decoded['SMSMessageData']['Recipients'] ?? [];
    $recipientStatus  = $recipients[0]['status']    ?? '';
    $msgId            = $recipients[0]['messageId'] ?? null;

    $looksLikeError = stripos($atMessage, 'invalid') !== false
        || stripos($atMessage, 'error')  !== false
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
            . " | BULK: " . ($bulkMode ? '1' : '0')
            . " | HTTP: $httpCode"
            . " | ERR: $curlErr"
            . " | $response\n",
        FILE_APPEND
    );

    return $success;
}

/**
 * Send a Bulk SMS to many recipients.
 * Uses the dedicated bulk endpoint in production.
 * Sandbox falls back to individual sends (bulk is "coming soon" in sandbox).
 *
 * Docs: https://developers.africastalking.com/docs/sms/sending/bulk
 *
 * @param array  $phoneNumbers  Array of recipient phone numbers
 * @param string $message       SMS body
 * @param string $senderId      Optional sender ID (production only)
 * @return array                ['success' => bool, 'message' => string, 'data' => array]
 */
function sendBulkSMS(array $phoneNumbers, $message, $senderId = null) {
    $username  = AT_USERNAME;
    $apiKey    = AT_API_KEY;
    $isSandbox = ($username === 'sandbox');

    // ============================================
    // SANDBOX FALLBACK
    // Bulk SMS sandbox endpoint is "coming soon" per AT docs.
    // We loop individually and return aggregated results.
    // ============================================
    if ($isSandbox) {
        $results = [
            'success' => true,
            'message' => 'Sandbox: sent individually.',
            'data'    => [],
        ];
        foreach ($phoneNumbers as $phone) {
            $results['data'][$phone] = sendSMS($phone, $message);
        }
        return $results;
    }

    // ============================================
    // PRODUCTION BULK ENDPOINT
    // ============================================
    $senderId = $senderId ?? AT_SENDER_ID;
    $url = 'https://api.africastalking.com/version1/messaging/bulk';

    $payload = [
        'username'     => $username,
        'message'      => $message,
        'senderId'     => $senderId,
        'phoneNumbers' => array_values($phoneNumbers),
        'enqueue'      => 1,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apiKey: ' . $apiKey,
        'Accept: application/json',
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    // File log
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    @file_put_contents(
        $logDir . '/bulk_sms.log',
        date('Y-m-d H:i:s')
            . " | COUNT: " . count($phoneNumbers)
            . " | HTTP: $httpCode"
            . " | ERR: $curlErr"
            . " | $response\n",
        FILE_APPEND
    );

    $decoded = json_decode($response, true);

    return [
        'success' => $httpCode === 200,
        'message' => $decoded['SMSMessageData']['Message'] ?? ($curlErr ?: 'Bulk send failed.'),
        'data'    => $decoded,
    ];
}
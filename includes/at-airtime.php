<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function at_send_airtime($phone, $amount, $currency = 'NGN') {
    $username = AT_USERNAME;
    $apiKey   = AT_API_KEY;

    $url = ($username === 'sandbox')
        ? 'https://api.sandbox.africastalking.com/version1/airtime/send'
        : 'https://api.africastalking.com/version1/airtime/send';

    // Format phone to +234
    $phone = preg_replace('/\s+/', '', (string)$phone);
    if (strpos($phone, '0') === 0) {
        $phone = '+234' . substr($phone, 1);
    }

    // CRITICAL FIX: Amount must be "NGN 50.00" (Currency Code + Space + Value)
    $formattedAmount = $currency . ' ' . number_format((float)$amount, 2);

    $recipients = json_encode([
        [
            'phoneNumber'  => $phone,
            'amount'       => $formattedAmount,
        ]
    ]);

    $data = [
        'username'   => $username,
        'recipients' => $recipients,
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

    log_write('airtime', "TO: $phone | AMOUNT: $formattedAmount | HTTP: $httpCode | ERR: $curlErr | $response");

    $decoded = json_decode($response, true);
    $success = false;
    $message = $curlErr ?: 'Airtime send failed.';
    
    if ($httpCode === 201 || $httpCode === 200) {
        // FIX: Africa's Talking returns "Sent" on success, not just "success"
        $status = isset($decoded['responses'][0]['status']) ? strtolower($decoded['responses'][0]['status']) : '';
        
        if (in_array($status, ['success', 'sent'])) {
            $success = true;
            $message = 'Success';
        } else {
            $message = $decoded['responses'][0]['errorMessage'] ?? $decoded['errorMessage'] ?? 'Sandbox rejected transaction';
        }
    }

    return ['success' => $success, 'message' => $message, 'data' => $decoded];
}

function queue_airtime_reward($phone, $amount, $reason, $referenceId = null) {
    agri_query("INSERT INTO agri_airtime_rewards (phone, amount, currency, reason, reference_id) VALUES (?, ?, 'NGN', ?, ?)", [$phone, $amount, $reason, $referenceId]);
    return agri_last_id();
}

function process_airtime_reward($rewardId) {
    $reward = agri_fetch_one("SELECT * FROM agri_airtime_rewards WHERE id = ? AND status = 'pending'", [(int)$rewardId]);
    if (!$reward) return ['success' => false, 'message' => 'Reward not found.'];
    
    $result = at_send_airtime($reward['phone'], (float)$reward['amount'], $reward['currency']);
    
    agri_query("UPDATE agri_airtime_rewards SET status = ?, at_response = ?, sent_at = NOW() WHERE id = ?", [$result['success'] ? 'sent' : 'failed', json_encode($result['data']), $reward['id']]);
    
    return $result;
}

function reward_price_submission($phone, $listingId) {
    $rewardId = queue_airtime_reward($phone, 50.00, 'price_submission', $listingId);
    return process_airtime_reward($rewardId);
}

function reward_verification_winner($phone, $listingId) {
    $rewardId = queue_airtime_reward($phone, 100.00, 'verification_winner', $listingId);
    return process_airtime_reward($rewardId);
}
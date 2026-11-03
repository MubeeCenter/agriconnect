<?php
/**
 * Africa's Talking Airtime Wrapper
 * Rewards farmers with airtime for verified price submissions.
 *
 * Docs: https://developers.africastalking.com/docs/airtime/overview
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Send airtime to a phone number.
 *
 * @param string $phone       Recipient phone (normalized 0XXXXXXXXXX)
 * @param float  $amount      Amount in NGN
 * @param string $currency    Currency code (default NGN)
 * @return array ['success'=>bool, 'message'=>string, 'data'=>array]
 */
function at_send_airtime($phone, $amount, $currency = 'NGN') {
    $username = AT_USERNAME;
    $apiKey   = AT_API_KEY;

    // Sandbox vs production URL
    $url = ($username === 'sandbox')
        ? 'https://api.sandbox.africastalking.com/version1/airtime/send'
        : 'https://api.africastalking.com/version1/airtime/send';

    // AT requires recipients as JSON array
    $recipients = json_encode([
        [
            'phoneNumber'  => $phone,
            'currencyCode' => $currency,
            'amount'       => $amount,
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

    // Log
    log_write('airtime',
        "TO: $phone | AMOUNT: $amount $currency | HTTP: $httpCode | ERR: $curlErr | $response");

    $decoded = json_decode($response, true);
    $success = ($httpCode === 200)
        && !empty($decoded['responses'][0]['status'])
        && strtolower($decoded['responses'][0]['status']) === 'success';

    return [
        'success' => $success,
        'message' => $decoded['responses'][0]['status'] ?? ($curlErr ?: 'Airtime send failed.'),
        'data'    => $decoded,
    ];
}

/**
 * Queue an airtime reward for a farmer.
 * Inserts into a new table `agri_airtime_rewards`.
 */
function queue_airtime_reward($phone, $amount, $reason, $referenceId = null) {
    // Create table if missing
    agri_query("
        CREATE TABLE IF NOT EXISTS agri_airtime_rewards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            phone VARCHAR(20) NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            currency VARCHAR(10) DEFAULT 'NGN',
            reason VARCHAR(100) NOT NULL,
            reference_id INT NULL,
            status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
            at_response TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            sent_at TIMESTAMP NULL,
            INDEX idx_phone (phone),
            INDEX idx_status (status),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    agri_query(
        "INSERT INTO agri_airtime_rewards (phone, amount, currency, reason, reference_id)
         VALUES (?, ?, 'NGN', ?, ?)",
        [$phone, $amount, $reason, $referenceId]
    );

    return agri_last_id();
}

/**
 * Process a pending airtime reward (actually send it).
 */
function process_airtime_reward($rewardId) {
    $reward = agri_fetch_one(
        "SELECT * FROM agri_airtime_rewards WHERE id = ? AND status = 'pending'",
        [(int)$rewardId]
    );

    if (!$reward) {
        return ['success' => false, 'message' => 'Reward not found or already processed.'];
    }

    $result = at_send_airtime($reward['phone'], (float)$reward['amount'], $reward['currency']);

    agri_query(
        "UPDATE agri_airtime_rewards
         SET status = ?, at_response = ?, sent_at = NOW()
         WHERE id = ?",
        [$result['success'] ? 'sent' : 'failed', json_encode($result['data']), $reward['id']]
    );

    return $result;
}

/**
 * Reward a farmer for a price submission.
 */
function reward_price_submission($phone, $listingId) {
    $rewardId = queue_airtime_reward($phone, 50.00, 'price_submission', $listingId);
    return process_airtime_reward($rewardId);
}

/**
 * Reward a farmer whose verification pushed a listing to Verified.
 */
function reward_verification_winner($phone, $listingId) {
    $rewardId = queue_airtime_reward($phone, 100.00, 'verification_winner', $listingId);
    return process_airtime_reward($rewardId);
}
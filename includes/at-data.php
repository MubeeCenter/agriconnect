<?php
/**
 * Africa's Talking Mobile Data Wrapper
 * Rewards high-engagement farmers with free data bundles.
 *
 * Docs: https://developers.africastalking.com/docs/data/overview
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Send a mobile data bundle to a phone number.
 *
 * @param string $phone       Recipient phone (normalized 0XXXXXXXXXX)
 * @param int    $quantity    Data quantity
 * @param string $unit        'MB' or 'GB'
 * @param string $validity    'Day', 'Week', 'Month'
 * @param string $productName AT payment product name (usually 'My Data Bundle')
 * @return array ['success'=>bool, 'message'=>string, 'data'=>array]
 */
function at_send_data($phone, $quantity, $unit = 'MB', $validity = 'Month', $productName = null) {
    $username    = AT_USERNAME;
    $apiKey      = AT_API_KEY;
    $productName = $productName ?? (getenv('AT_DATA_PRODUCT') ?: 'AgriConnect Rewards');

    // Mobile Data uses the Payments API endpoint
    $url = ($username === 'sandbox')
        ? 'https://payments.sandbox.africastalking.com/mobile/data/request'
        : 'https://payments.africastalking.com/mobile/data/request';

    $data = [
        'username'    => $username,
        'productName' => $productName,
        'recipients'  => json_encode([[
            'phoneNumber' => $phone,
            'quantity'    => $quantity,
            'unit'        => $unit,
            'validity'    => $validity,
        ]]),
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

    log_write('data',
        "TO: $phone | QTY: $quantity $unit | VALIDITY: $validity | HTTP: $httpCode | ERR: $curlErr | $response");

    $decoded = json_decode($response, true);
    $success = ($httpCode === 200)
        && !empty($decoded['responses'][0]['status'])
        && strtolower($decoded['responses'][0]['status']) === 'success';

    return [
        'success' => $success,
        'message' => $decoded['responses'][0]['status'] ?? ($curlErr ?: 'Data send failed.'),
        'data'    => $decoded,
    ];
}

/**
 * Reward a high-engagement farmer with a free data bundle.
 */
function reward_data_bundle($phone, $reason = 'loyalty') {
    // Create table if missing
    agri_query("
        CREATE TABLE IF NOT EXISTS agri_data_rewards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            phone VARCHAR(20) NOT NULL,
            quantity INT NOT NULL,
            unit VARCHAR(5) DEFAULT 'MB',
            validity VARCHAR(10) DEFAULT 'Month',
            reason VARCHAR(100) NOT NULL,
            status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
            at_response TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            sent_at TIMESTAMP NULL,
            INDEX idx_phone (phone),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    agri_query(
        "INSERT INTO agri_data_rewards (phone, quantity, unit, validity, reason)
         VALUES (?, 500, 'MB', 'Month', ?)",
        [$phone, $reason]
    );

    $rewardId = agri_last_id();
    $result = at_send_data($phone, 500, 'MB', 'Month');

    agri_query(
        "UPDATE agri_data_rewards SET status = ?, at_response = ?, sent_at = NOW() WHERE id = ?",
        [$result['success'] ? 'sent' : 'failed', json_encode($result['data']), $rewardId]
    );

    return $result;
}

/**
 * Check if a farmer qualifies for the data reward (10+ verifications).
 */
function farmer_qualifies_for_data($phone) {
    $row = agri_fetch_one(
        "SELECT COUNT(*) AS c FROM agri_verifications WHERE phone = ?",
        [$phone]
    );
    return ((int)($row['c'] ?? 0)) >= 10;
}
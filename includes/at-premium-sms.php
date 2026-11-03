<?php
/**
 * Africa's Talking Premium SMS Wrapper
 * Paid real-time price alerts for commodity buyers and traders.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Create a premium subscription tier for a buyer.
 * Buyers pay ₦5,000/month to receive real-time price movements.
 */
function premium_sms_subscribe($phone, $plan = 'traders_monthly') {
    agri_query("
        CREATE TABLE IF NOT EXISTS agri_premium_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            phone VARCHAR(20) UNIQUE NOT NULL,
            plan VARCHAR(50) DEFAULT 'traders_monthly',
            price DECIMAL(10, 2) DEFAULT 5000.00,
            currency VARCHAR(10) DEFAULT 'NGN',
            status ENUM('active', 'cancelled', 'expired') DEFAULT 'active',
            subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at DATE NULL,
            cancelled_at TIMESTAMP NULL,
            INDEX idx_phone (phone),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $existing = agri_fetch_one(
        "SELECT id FROM agri_premium_subscribers WHERE phone = ?",
        [$phone]
    );

    if ($existing) {
        agri_query(
            "UPDATE agri_premium_subscribers
             SET status = 'active', expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY)
             WHERE phone = ?",
            [$phone]
        );
        return ['success' => true, 'message' => 'Subscription renewed.'];
    }

    agri_query(
        "INSERT INTO agri_premium_subscribers (phone, plan, price, expires_at)
         VALUES (?, ?, 5000.00, DATE_ADD(NOW(), INTERVAL 30 DAY))",
        [$phone, $plan]
    );

    return ['success' => true, 'message' => 'Premium subscription activated.'];
}

/**
 * Cancel a premium subscription.
 */
function premium_sms_unsubscribe($phone) {
    agri_query(
        "UPDATE agri_premium_subscribers
         SET status = 'cancelled', cancelled_at = NOW()
         WHERE phone = ? AND status = 'active'",
        [$phone]
    );
    return ['success' => true, 'message' => 'Subscription cancelled.'];
}

/**
 * Broadcast a real-time price move to all active premium subscribers.
 */
function premium_broadcast_price_move($listingId) {
    $listing = agri_fetch_one("SELECT * FROM agri_listings WHERE id = ?", [(int)$listingId]);
    if (!$listing) return ['success' => false, 'message' => 'Listing not found.'];

    $subscribers = agri_fetch_all(
        "SELECT phone FROM agri_premium_subscribers
         WHERE status = 'active' AND (expires_at IS NULL OR expires_at >= CURDATE())"
    );

    if (empty($subscribers)) {
        return ['success' => true, 'message' => 'No active premium subscribers.'];
    }

    $message = "PREMIUM ALERT: {$listing['crop']} in {$listing['market']} is now "
        . formatNaira($listing['price'])
        . "/ton. Verified by {$listing['verification_count']} farmers. Reply STOP to cancel.";

    $phones = array_column($subscribers, 'phone');
    return sendBulkSMS($phones, $message);
}

/**
 * Count active premium subscribers (for admin stats).
 */
function premium_subscriber_count() {
    $row = agri_fetch_one(
        "SELECT COUNT(*) AS c FROM agri_premium_subscribers
         WHERE status = 'active' AND (expires_at IS NULL OR expires_at >= CURDATE())"
    );
    return (int)($row['c'] ?? 0);
}
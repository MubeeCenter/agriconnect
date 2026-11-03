<?php
/**
 * Africa's Talking Insights Wrapper
 * Pulls engagement metrics for the admin "Market Health" dashboard.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Fetch engagement metrics from AT Insights API.
 *
 * Note: AT Insights API access requires enabling the product on your account.
 * If not available, we compute equivalent metrics from our own tables
 * (agri_sms_logs, agri_verifications, agri_activity_log).
 *
 * @return array
 */
function get_market_health_metrics() {
    // ---- Primary metrics from OUR database (always available) ----
    $metrics = [];

    // Verifications per region (last 30 days)
    $metrics['verifications_by_region'] = agri_fetch_all("
        SELECT l.market,
               COUNT(v.id) AS total
        FROM agri_verifications v
        LEFT JOIN agri_listings l ON l.id = v.listing_id
        WHERE v.verified_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY l.market
        ORDER BY total DESC
    ");

    // Verifications per crop (last 30 days)
    $metrics['verifications_by_crop'] = agri_fetch_all("
        SELECT l.crop,
               COUNT(v.id) AS total
        FROM agri_verifications v
        LEFT JOIN agri_listings l ON l.id = v.listing_id
        WHERE v.verified_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY l.crop
        ORDER BY total DESC
    ");

    // SMS activity (sent + received, last 30 days)
    $metrics['sms_activity'] = agri_fetch_all("
        SELECT DATE(created_at) AS day,
               SUM(CASE WHEN direction = 'outbound' THEN 1 ELSE 0 END) AS sent,
               SUM(CASE WHEN direction = 'inbound'  THEN 1 ELSE 0 END) AS received
        FROM agri_sms_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY day DESC
    ");

    // Top dealers by farmer engagement (voice + SMS inquiries)
    $metrics['top_dealers'] = agri_fetch_all("
        SELECT d.business_name, d.state,
               COUNT(vq.id) AS call_requests
        FROM agri_dealers d
        LEFT JOIN agri_voice_queue vq ON vq.dealer_phone = d.phone
        WHERE vq.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY d.id
        ORDER BY call_requests DESC
        LIMIT 10
    ");

    // Total engagement counters
    $metrics['totals'] = [
        'farmers'       => countFarmers(),
        'verifications' => countVerifications(),
        'voice_calls'   => (int)(agri_fetch_one(
            "SELECT COUNT(*) AS c FROM agri_voice_queue WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        )['c'] ?? 0),
        'airtime_rewards' => (int)(agri_fetch_one(
            "SELECT COALESCE(SUM(amount), 0) AS s FROM agri_airtime_rewards WHERE status = 'sent'"
        )['s'] ?? 0),
    ];

    return $metrics;
}
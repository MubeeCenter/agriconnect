<?php
/**
 * AgriConnect Helper Functions
 * Farmer management, logging, formatting, shared UI helpers, admin helpers
 *
 * Loads: config.php, db.php, session.php (safe to require anywhere)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Session is safe to require — it self-guards against double-start
if (file_exists(__DIR__ . '/session.php')) {
    require_once __DIR__ . '/session.php';
}

// ============================================
// FARMER MANAGEMENT
// ============================================

function ensureFarmerExists($phone) {
    if (empty($phone)) return false;

    $existing = agri_fetch_one(
        "SELECT id FROM agri_farmers WHERE phone = ?",
        [$phone]
    );

    if (!$existing) {
        agri_query(
            "INSERT INTO agri_farmers (phone, state) VALUES (?, 'Kano')",
            [$phone]
        );
    }
    return true;
}

function getFarmer($phone) {
    return agri_fetch_one(
        "SELECT * FROM agri_farmers WHERE phone = ?",
        [$phone]
    );
}

function setFarmerState($phone, $state) {
    agri_query(
        "UPDATE agri_farmers SET state = ? WHERE phone = ?",
        [$state, $phone]
    );
}

function enableFarmerAlerts($phone) {
    agri_query(
        "UPDATE agri_farmers SET sms_alerts_enabled = 1 WHERE phone = ?",
        [$phone]
    );
}

function disableFarmerAlerts($phone) {
    agri_query(
        "UPDATE agri_farmers SET sms_alerts_enabled = 0 WHERE phone = ?",
        [$phone]
    );
}

function countFarmers() {
    $row = agri_fetch_one("SELECT COUNT(*) AS c FROM agri_farmers");
    return (int)($row['c'] ?? 0);
}

// ============================================
// PHONE NORMALIZATION HELPERS
// ============================================

/**
 * Normalize a Nigerian phone number to 0XXXXXXXXXX form.
 * Accepts: 080..., +23480..., 23480...
 */
function normalize_phone($phone) {
    $phone = preg_replace('/\s+/', '', (string)$phone);

    if (strpos($phone, '+234') === 0) {
        $phone = '0' . substr($phone, 4);
    } elseif (strpos($phone, '234') === 0 && strlen($phone) > 3) {
        $phone = '0' . substr($phone, 3);
    }

    return $phone;
}

/**
 * Is this a valid Nigerian mobile number?
 */
function is_valid_phone_ng($phone) {
    return (bool)preg_match('/^0[789][01]\d{8}$/', normalize_phone($phone));
}

// ============================================
// LISTING & PRICE HELPERS
// ============================================

function getLatestPrice($crop, $market) {
    return agri_fetch_one(
        "SELECT * FROM agri_listings
         WHERE crop = ? AND market = ?
         ORDER BY updated_at DESC LIMIT 1",
        [$crop, $market]
    );
}

function getListingById($id) {
    return agri_fetch_one(
        "SELECT * FROM agri_listings WHERE id = ?",
        [(int)$id]
    );
}

/**
 * Find the most recent listing relevant to a farmer's region.
 * Used by the SMS webhook to decide which listing a "YES" reply verifies.
 */
function findLatestListingForFarmer($phone) {
    $farmer = getFarmer($phone);
    $state  = $farmer['state'] ?? 'Kano';

    return agri_fetch_one(
        "SELECT * FROM agri_listings
         WHERE market = ?
         ORDER BY updated_at DESC
         LIMIT 1",
        [$state]
    );
}

/**
 * Has this phone already verified this listing?
 */
function hasUserVerifiedListing($listingId, $phone) {
    $row = agri_fetch_one(
        "SELECT id FROM agri_verifications
         WHERE listing_id = ? AND phone = ?
         LIMIT 1",
        [(int)$listingId, normalize_phone($phone)]
    );
    return !empty($row);
}

/**
 * Record a verification. Returns success flag and message.
 */
function recordVerification($listingId, $phone) {
    $phone = normalize_phone($phone);
    $listingId = (int)$listingId;

    $existing = agri_fetch_one(
        "SELECT id FROM agri_verifications WHERE listing_id = ? AND phone = ?",
        [$listingId, $phone]
    );

    if ($existing) {
        return ['success' => false, 'message' => 'You already verified this price.'];
    }

    agri_query(
        "INSERT INTO agri_verifications (listing_id, phone) VALUES (?, ?)",
        [$listingId, $phone]
    );

    agri_query(
        "UPDATE agri_listings SET verification_count = verification_count + 1 WHERE id = ?",
        [$listingId]
    );

    $listing = agri_fetch_one(
        "SELECT verification_count FROM agri_listings WHERE id = ?",
        [$listingId]
    );

    if ($listing && $listing['verification_count'] >= USSD_VERIFY_THRESHOLD) {
        agri_query(
            "UPDATE agri_listings SET is_verified = 1 WHERE id = ?",
            [$listingId]
        );
    }

    return ['success' => true, 'message' => 'Thank you! Your verification has been recorded.'];
}

function countListings($verifiedOnly = false) {
    $sql = "SELECT COUNT(*) AS c FROM agri_listings";
    if ($verifiedOnly) $sql .= " WHERE is_verified = 1";
    $row = agri_fetch_one($sql);
    return (int)($row['c'] ?? 0);
}

function countListingsPending() {
    $row = agri_fetch_one(
        "SELECT COUNT(*) AS c FROM agri_listings WHERE is_verified = 0"
    );
    return (int)($row['c'] ?? 0);
}

function getRecentListings($limit = 10) {
    return agri_fetch_all(
        "SELECT * FROM agri_listings
         ORDER BY updated_at DESC
         LIMIT " . (int)$limit
    );
}

// ============================================
// DEALER HELPERS
// ============================================

function getDealersByMarket($market, $limit = 5) {
    return agri_fetch_all(
        "SELECT * FROM agri_dealers
         WHERE state = ? AND is_verified = 1
         ORDER BY business_name ASC
         LIMIT " . (int)$limit,
        [$market]
    );
}

function getDealerById($id) {
    return agri_fetch_one(
        "SELECT * FROM agri_dealers WHERE id = ?",
        [(int)$id]
    );
}

function countDealers($verifiedOnly = false) {
    $sql = "SELECT COUNT(*) AS c FROM agri_dealers";
    if ($verifiedOnly) $sql .= " WHERE is_verified = 1";
    $row = agri_fetch_one($sql);
    return (int)($row['c'] ?? 0);
}

function countDealersPending() {
    $row = agri_fetch_one(
        "SELECT COUNT(*) AS c FROM agri_dealers WHERE is_verified = 0"
    );
    return (int)($row['c'] ?? 0);
}

// ============================================
// SMS / VERIFICATION / VOICE STATS
// ============================================

function countVerifications() {
    $row = agri_fetch_one("SELECT COUNT(*) AS c FROM agri_verifications");
    return (int)($row['c'] ?? 0);
}

function getRecentVerifications($limit = 10) {
    return agri_fetch_all(
        "SELECT v.*, l.crop, l.market
         FROM agri_verifications v
         LEFT JOIN agri_listings l ON l.id = v.listing_id
         ORDER BY v.verified_at DESC
         LIMIT " . (int)$limit
    );
}

function countSmsSent() {
    $row = agri_fetch_one(
        "SELECT COUNT(*) AS c FROM agri_sms_logs WHERE direction = 'outbound' AND status = 'sent'"
    );
    return (int)($row['c'] ?? 0);
}

function countSmsReceived() {
    $row = agri_fetch_one(
        "SELECT COUNT(*) AS c FROM agri_sms_logs WHERE direction = 'inbound'"
    );
    return (int)($row['c'] ?? 0);
}

function getRecentSmsLogs($limit = 10) {
    return agri_fetch_all(
        "SELECT * FROM agri_sms_logs
         ORDER BY created_at DESC
         LIMIT " . (int)$limit
    );
}

function getPendingVoiceCalls($limit = 10) {
    return agri_fetch_all(
        "SELECT * FROM agri_voice_queue
         WHERE status = 'pending'
         ORDER BY created_at DESC
         LIMIT " . (int)$limit
    );
}

// ============================================
// CONTACT MESSAGES
// ============================================

function countUnreadContacts() {
    $row = agri_fetch_one("SELECT COUNT(*) AS c FROM agri_contacts WHERE is_read = 0");
    return (int)($row['c'] ?? 0);
}

function getRecentContacts($limit = 10) {
    return agri_fetch_all(
        "SELECT * FROM agri_contacts
         ORDER BY created_at DESC
         LIMIT " . (int)$limit
    );
}

// ============================================
// DASHBOARD — COMPOSITE STATS
// ============================================

/**
 * One-shot stats pull for the admin dashboard.
 */
function getDashboardStats() {
    return [
        'farmers'             => countFarmers(),
        'listings_total'      => countListings(false),
        'listings_verified'   => countListings(true),
        'listings_pending'    => countListingsPending(),
        'dealers_total'       => countDealers(false),
        'dealers_verified'    => countDealers(true),
        'dealers_pending'     => countDealersPending(),
        'verifications'       => countVerifications(),
        'sms_sent'            => countSmsSent(),
        'sms_received'        => countSmsReceived(),
        'contacts_unread'     => countUnreadContacts(),
        'voice_pending'       => function_exists('voice_queue_pending_count')
                                    ? voice_queue_pending_count() : 0,
    ];
}

// ============================================
// LOGGING
// ============================================

function agri_log($phone, $action, $details = '') {
    agri_query(
        "INSERT INTO agri_activity_log (phone, action, details, ip_address)
         VALUES (?, ?, ?, ?)",
        [$phone, $action, $details, $_SERVER['REMOTE_ADDR'] ?? '']
    );
}

function log_ussd($phone, $text, $level) {
    $logDir = LOG_DIR;
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);

    @file_put_contents(
        $logDir . '/ussd_activity.log',
        date('Y-m-d H:i:s') . " | $phone | L$level | $text\n",
        FILE_APPEND
    );
}

function log_write($channel, $message) {
    $logDir = LOG_DIR;
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);

    @file_put_contents(
        $logDir . '/' . preg_replace('/[^a-z0-9_-]/i', '', $channel) . '.log',
        date('Y-m-d H:i:s') . " | " . $message . "\n",
        FILE_APPEND
    );
}

// ============================================
// FORMATTING
// ============================================

function formatNaira($amount) {
    return 'N' . number_format((float)$amount, 0, '.', ',');
}

function formatNumber($n) {
    return number_format((float)$n, 0, '.', ',');
}

function timeAgo($timestamp) {
    if (!$timestamp) return 'never';
    $diff = time() - strtotime($timestamp);
    if ($diff < 0)      return 'just now';
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60) . ' min ago';
    if ($diff < 86400)  return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M j, Y', strtotime($timestamp));
}

function formatDate($timestamp, $fmt = 'M j, Y g:i A') {
    if (!$timestamp) return '—';
    return date($fmt, strtotime($timestamp));
}

function shortPhone($phone) {
    $p = (string)$phone;
    if (strlen($p) <= 6) return $p;
    return substr($p, 0, 3) . '****' . substr($p, -2);
}

// ============================================
// ESCAPING HELPERS
// ============================================

function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function e_attr($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ============================================
// BADGE RENDERERS
// ============================================

function badge_verified($isVerified, $count = 0) {
    if ($isVerified) {
        $label = 'Verified';
        if ($count > 0) $label .= " ({$count})";
        return '<span class="agri-badge agri-badge-success">' . e($label) . '</span>';
    }
    if ($count > 0) {
        return '<span class="agri-badge agri-badge-warning">Pending (' . (int)$count . '/' . USSD_VERIFY_THRESHOLD . ')</span>';
    }
    return '<span class="agri-badge agri-badge-gray">New</span>';
}

function badge_status($status) {
    $map = [
        'sent'      => 'agri-badge-success',
        'received'  => 'agri-badge-info',
        'failed'    => 'agri-badge-danger',
        'pending'   => 'agri-badge-warning',
        'connected' => 'agri-badge-success',
        'completed' => 'agri-badge-success',
    ];
    $cls = $map[strtolower($status)] ?? 'agri-badge-gray';
    return '<span class="agri-badge ' . $cls . '">' . e(ucfirst($status)) . '</span>';
}

// ============================================
// REDIRECT + FLASH SHORTCUTS
// ============================================

/**
 * Redirect to a URL and exit.
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Set flash + redirect (the most common admin pattern).
 */
function redirect_with($url, $type, $message) {
    if (function_exists('flash_set')) {
        flash_set($type, $message);
    }
    redirect($url);
}

/**
 * Redirect back to the referring page, or a fallback.
 */
function redirect_back($fallback = '/admin/dashboard.php') {
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    // Only trust same-origin referrers
    if ($ref && strpos($ref, BASE_URL) === 0) {
        redirect($ref);
    }
    redirect(BASE_URL . $fallback);
}

// ============================================
// PAGINATION HELPERS
// ============================================

function paginate($countSql, $countParams, $rowSql, $rowParams, $page = 1, $perPage = ADMIN_PAGE_SIZE) {
    $page    = max(1, (int)$page);
    $perPage = max(1, (int)$perPage);

    $totalRow = agri_fetch_one($countSql, $countParams);
    $total    = (int)($totalRow['c'] ?? 0);
    $pages    = max(1, (int)ceil($total / $perPage));

    if ($page > $pages) $page = $pages;
    $offset = ($page - 1) * $perPage;

    $rowSql .= " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
    $rows = agri_fetch_all($rowSql, $rowParams);

    return [
        'rows'     => $rows,
        'total'    => $total,
        'page'     => $page,
        'pages'    => $pages,
        'per_page' => $perPage,
    ];
}

/**
 * Render pagination links.
 * Preserves existing query-string filters — only replaces `page`.
 */
function render_pagination($page, $pages, $baseUrl) {
    if ($pages <= 1) return '';

    // Strip existing page= from baseUrl
    $urlParts = parse_url($baseUrl);
    $query    = [];
    if (!empty($urlParts['query'])) {
        parse_str($urlParts['query'], $query);
    }
    unset($query['page']);

    $buildUrl = function ($p) use ($urlParts, $query) {
        $q = $query;
        $q['page'] = $p;
        $path = $urlParts['path'] ?? '';
        return $path . '?' . http_build_query($q);
    };

    $html = '<ul class="admin-pagination">';

    if ($page > 1) {
        $html .= '<li><a href="' . e($buildUrl($page - 1)) . '">&laquo;</a></li>';
    }

    $start = max(1, $page - 2);
    $end   = min($pages, $page + 2);
    for ($i = $start; $i <= $end; $i++) {
        if ($i === $page) {
            $html .= '<li class="is-active"><span>' . $i . '</span></li>';
        } else {
            $html .= '<li><a href="' . e($buildUrl($i)) . '">' . $i . '</a></li>';
        }
    }

    if ($page < $pages) {
        $html .= '<li><a href="' . e($buildUrl($page + 1)) . '">&raquo;</a></li>';
    }

    $html .= '</ul>';
    return $html;
}

// ============================================
// MENU BUILDERS (reused across USSD states)
// ============================================

function mainMenu() {
    return "CON Welcome to AgriConnect\n" .
           "1. Check Market Prices\n" .
           "2. Find Agro-Dealer\n" .
           "3. Report Pest/Disease\n" .
           "4. My Account";
}

function cropMenu() {
    return "CON Select Crop:\n1. Maize\n2. Cowpea\n3. Tomato\n0. Back";
}

function marketMenu($prefix = "Select Market") {
    return "CON $prefix:\n1. Kano\n2. Kaduna\n3. Abuja\n0. Back";
}

function mapCrop($input) {
    $map = ['1' => 'Maize', '2' => 'Cowpea', '3' => 'Tomato'];
    return $map[$input] ?? null;
}

function mapMarket($input) {
    $map = ['1' => 'Kano', '2' => 'Kaduna', '3' => 'Abuja'];
    return $map[$input] ?? null;
}

// ============================================
// COMPOSITE BOOTSTRAP FOR ADMIN PAGES
// ============================================

/**
 * Load every include an admin page needs + enforce login.
 * Self-sufficient: requires functions.php first, then its dependencies.
 *
 * Usage: require_once __DIR__.'/../includes/functions.php'; agri_boot_admin();
 */
function agri_boot_admin() {
    // Ensure our own file is loaded (in case caller didn't)
    if (!function_exists('agri_fetch_one')) {
        require_once __DIR__ . '/functions.php';
    }

    $base = __DIR__;
    foreach ([
        'session.php',
        'auth.php',
        'validation.php',
        'at-sms.php',
        'at-voice.php',
        'mail.php',
    ] as $f) {
        if (file_exists($base . '/' . $f)) {
            require_once $base . '/' . $f;
        }
    }

    if (function_exists('auth_require_login')) {
        auth_require_login();
    }
}

/**
 * Load every include a public page needs (no login guard).
 */
function agri_boot_public() {
    if (!function_exists('agri_fetch_one')) {
        require_once __DIR__ . '/functions.php';
    }

    $base = __DIR__;
    foreach ([
        'session.php',
        'validation.php',
        'at-sms.php',
        'mail.php',
    ] as $f) {
        if (file_exists($base . '/' . $f)) {
            require_once $base . '/' . $f;
        }
    }
}

// ============================================
// CSRF SHORTCUT (guards against missing session.php)
// ============================================

if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="_csrf" value="">';
    }
    function csrf_require() {
        // No session loaded → silently allow (dev safety)
    }
    function csrf_verify($token = null) {
        return true;
    }
}
<?php
/**
 * AgriConnect Configuration
 * All credentials, environment settings, constants
 *
 * NOTE: Real secrets are loaded from includes/secrets.php (not tracked).
 *       If secrets.php is missing, sandbox defaults are used.
 */

// ============================================
// PREVENT DIRECT ACCESS
// ============================================
if (!defined('AGRICONNECT')) {
    define('AGRICONNECT', true);
}

// ============================================
// LOAD SECRETS (outside version control)
// ============================================
$__secrets_file = __DIR__ . '/secrets.php';
if (file_exists($__secrets_file)) {
    require_once $__secrets_file;
}
unset($__secrets_file);

// ============================================
// AFRICA'S TALKING CREDENTIALS
// ============================================
defined('AT_USERNAME')  || define('AT_USERNAME',  'sandbox');
defined('AT_API_KEY')   || define('AT_API_KEY',   'atsk_your_api_key_here');
defined('AT_SENDER_ID') || define('AT_SENDER_ID', 'AgriConnect');
defined('AT_USSD_CODE') || define('AT_USSD_CODE', '*384*0032#');

// ============================================
// ENVIRONMENT
// ============================================
defined('APP_ENV')     || define('APP_ENV',     'development');
defined('BASE_URL')    || define('BASE_URL',    'https://biz.mubeetech.com.ng');
defined('APP_NAME')    || define('APP_NAME',    'AgriConnect');
defined('APP_VERSION') || define('APP_VERSION', '1.0.0');

// ============================================
// MAIL
// ============================================
defined('MAIL_FROM_EMAIL')  || define('MAIL_FROM_EMAIL',  'no-reply@mubeetech.com.ng');
defined('MAIL_FROM_NAME')   || define('MAIL_FROM_NAME',   'AgriConnect');
defined('MAIL_ADMIN_EMAIL') || define('MAIL_ADMIN_EMAIL', 'admin@mubeetech.com.ng');

// ============================================
// SESSION
// ============================================
defined('SESSION_NAME')             || define('SESSION_NAME', 'AGRICONNECT_SESS');
defined('SESSION_LIFETIME_SECONDS') || define('SESSION_LIFETIME_SECONDS', 7200);

// ============================================
// BUSINESS RULES
// ============================================
defined('USSD_VERIFY_THRESHOLD')   || define('USSD_VERIFY_THRESHOLD', 3);
defined('MAX_DEALERS_PER_MENU')    || define('MAX_DEALERS_PER_MENU', 5);
defined('SESSION_TIMEOUT_MINUTES') || define('SESSION_TIMEOUT_MINUTES', 5);
defined('ADMIN_PAGE_SIZE')         || define('ADMIN_PAGE_SIZE', 25);

// Airtime reward amounts (NGN)
defined('AIRTIME_REWARD_SUBMISSION') || define('AIRTIME_REWARD_SUBMISSION', (float)(getenv('AIRTIME_REWARD_SUBMISSION') ?: 50));
defined('AIRTIME_REWARD_VERIFIED')   || define('AIRTIME_REWARD_VERIFIED',   (float)(getenv('AIRTIME_REWARD_VERIFIED')   ?: 100));

// Mobile Data reward
defined('DATA_REWARD_QUANTITY_MB') || define('DATA_REWARD_QUANTITY_MB', (int)(getenv('DATA_REWARD_QUANTITY_MB') ?: 500));
defined('DATA_REWARD_THRESHOLD')   || define('DATA_REWARD_THRESHOLD',   (int)(getenv('DATA_REWARD_THRESHOLD')   ?: 10)); // verifications

// Cron key for web-triggered broadcasts
defined('CRON_KEY') || define('CRON_KEY', getenv('CRON_KEY') ?: 'change-me-to-a-long-random-string');

// ============================================
// LOG PATHS
// ============================================
defined('LOG_DIR') || define('LOG_DIR', dirname(__DIR__) . '/logs');

if (!is_dir(LOG_DIR)) {
    @mkdir(LOG_DIR, 0755, true);
}

// ============================================
// TIMEZONE & ERROR HANDLING
// ============================================
date_default_timezone_set('Africa/Lagos');

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', LOG_DIR . '/error.log');
error_reporting(E_ALL);

// ============================================
// GLOBAL ERROR + EXCEPTION HANDLERS
// ============================================
set_exception_handler(function ($e) {
    error_log('Uncaught Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent() && php_sapi_name() !== 'cli') {
        http_response_code(500);
    }
    if (!empty($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/api/ussd.php') !== false) {
        header('Content-Type: text/plain');
        echo 'END Service error. Please try again later.';
    } else {
        echo 'An unexpected error occurred. Please try again.';
    }
    exit;
});

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// ============================================
// SECURITY HEADERS (skip for CLI + USSD endpoint)
// ============================================
if (!headers_sent() && php_sapi_name() !== 'cli') {
    $isUssdApi = !empty($_SERVER['SCRIPT_NAME'])
        && strpos($_SERVER['SCRIPT_NAME'], '/api/ussd.php') !== false;

    if (!$isUssdApi) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(self), microphone=(), camera=()');
    }
}

// ============================================
// CONVENIENCE FLAGS
// ============================================
defined('IS_PRODUCTION')  || define('IS_PRODUCTION',  APP_ENV === 'production');
defined('IS_DEVELOPMENT') || define('IS_DEVELOPMENT', APP_ENV === 'development');
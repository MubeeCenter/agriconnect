<?php
/**
 * AgriConnect Configuration
 * All credentials, environment settings, constants
 *
 * Resolution order for credentials:
 *   1. Environment variables (Docker, Marketplace deployments)
 *   2. includes/secrets.php (cPanel, shared hosting)
 *   3. Hardcoded fallback defaults (sandbox / dev)
 */

// ============================================
// PREVENT DIRECT ACCESS
// ============================================
if (!defined('AGRICONNECT')) {
    define('AGRICONNECT', true);
}

// ============================================
// LOAD SECRETS FILE (if present)
// ============================================
// On cPanel/shared hosting, credentials live in includes/secrets.php.
// In Docker, secrets.php is excluded and env vars are used instead.
$__secrets_file = __DIR__ . '/secrets.php';
if (file_exists($__secrets_file)) {
    require_once $__secrets_file;
}
unset($__secrets_file);

// ============================================
// AFRICA'S TALKING CREDENTIALS
// ============================================
// Precedence: env var > secrets.php > fallback
defined('AT_USERNAME')  || define('AT_USERNAME',  getenv('AT_USERNAME')  ?: 'sandbox');
defined('AT_API_KEY')   || define('AT_API_KEY',   getenv('AT_API_KEY')   ?: 'atsk_your_api_key_here');
defined('AT_SENDER_ID') || define('AT_SENDER_ID', getenv('AT_SENDER_ID') ?: 'AgriConnect');
defined('AT_USSD_CODE') || define('AT_USSD_CODE', getenv('AT_USSD_CODE') ?: '*384*0032#');

// ============================================
// ENVIRONMENT
// ============================================
defined('APP_ENV')     || define('APP_ENV',     getenv('APP_ENV')     ?: 'development');
defined('BASE_URL')    || define('BASE_URL',    getenv('BASE_URL')    ?: 'https://biz.mubeetech.com.ng');
defined('APP_NAME')    || define('APP_NAME',    getenv('APP_NAME')    ?: 'AgriConnect');
defined('APP_VERSION') || define('APP_VERSION', getenv('APP_VERSION') ?: '1.0.0');

// ============================================
// MAIL
// ============================================
defined('MAIL_FROM_EMAIL')  || define('MAIL_FROM_EMAIL',  getenv('MAIL_FROM_EMAIL')  ?: 'no-reply@mubeetech.com.ng');
defined('MAIL_FROM_NAME')   || define('MAIL_FROM_NAME',   getenv('MAIL_FROM_NAME')   ?: 'AgriConnect');
defined('MAIL_ADMIN_EMAIL') || define('MAIL_ADMIN_EMAIL', getenv('MAIL_ADMIN_EMAIL') ?: 'admin@mubeetech.com.ng');

// ============================================
// SESSION
// ============================================
defined('SESSION_NAME')              || define('SESSION_NAME',              getenv('SESSION_NAME')              ?: 'AGRICONNECT_SESS');
defined('SESSION_LIFETIME_SECONDS')  || define('SESSION_LIFETIME_SECONDS',  (int)(getenv('SESSION_LIFETIME_SECONDS') ?: 7200));

// ============================================
// BUSINESS RULES
// ============================================
defined('USSD_VERIFY_THRESHOLD')   || define('USSD_VERIFY_THRESHOLD',   (int)(getenv('USSD_VERIFY_THRESHOLD')   ?: 3));
defined('MAX_DEALERS_PER_MENU')    || define('MAX_DEALERS_PER_MENU',    (int)(getenv('MAX_DEALERS_PER_MENU')    ?: 5));
defined('SESSION_TIMEOUT_MINUTES') || define('SESSION_TIMEOUT_MINUTES', (int)(getenv('SESSION_TIMEOUT_MINUTES') ?: 5));
defined('ADMIN_PAGE_SIZE')         || define('ADMIN_PAGE_SIZE',         (int)(getenv('ADMIN_PAGE_SIZE')         ?: 25));

// ============================================
// LOG PATHS
// ============================================
defined('LOG_DIR') || define('LOG_DIR', getenv('LOG_DIR') ?: (dirname(__DIR__) . '/logs'));

// Ensure log directory exists
if (!is_dir(LOG_DIR)) {
    @mkdir(LOG_DIR, 0755, true);
}

// ============================================
// TIMEZONE & ERROR HANDLING
// ============================================
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Africa/Lagos');

// Never leak errors to output (USSD responses must stay clean)
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
    // If this is a USSD request, respond with a safe message
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
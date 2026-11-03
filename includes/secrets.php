<?php
/**
 * AgriConnect — Secrets
 * Keep this file OUTSIDE version control.
 */

if (!defined('AGRICONNECT')) {
    http_response_code(403);
    exit('Forbidden');
}

// ============================================
// AFRICA'S TALKING
// ============================================
define('AT_USERNAME',  'sandbox');
define('AT_API_KEY',   'atsk_807cc17fcf9f7806921a8472bbefb6f6b9676286812ecd4623ff9159ea599d5f50eb811f');
define('AT_SENDER_ID', 'AgriConnect');
define('AT_USSD_CODE', '*384*0032#');

// ============================================
// ENVIRONMENT
// ============================================
define('APP_ENV',  'development');
define('BASE_URL', 'https://biz.mubeetech.com.ng');

// ============================================
// CRON KEY — protects the broadcast endpoint
// Generate: php -r "echo 'cron_' . bin2hex(random_bytes(24)) . PHP_EOL;"
// ============================================
define('CRON_KEY', 'cron_REPLACE_WITH_YOUR_GENERATED_KEY');
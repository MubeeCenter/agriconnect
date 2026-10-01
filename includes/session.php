<?php
/**
 * AgriConnect — Session Management
 * Safe session initialization + flash messages + CSRF token
 */

require_once __DIR__ . '/config.php';

// ============================================
// SAFE SESSION START
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    // Session hardening
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');

    // Secure cookies over HTTPS
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }

    session_name('AGRICONNECT_SESS');
    session_start();
}

// ============================================
// SESSION REGENERATION (prevent fixation)
// ============================================
if (!isset($_SESSION['_created'])) {
    $_SESSION['_created'] = time();
} elseif (time() - $_SESSION['_created'] > 1800) {
    // Regenerate every 30 minutes
    session_regenerate_id(true);
    $_SESSION['_created'] = time();
}

// ============================================
// SESSION HELPERS
// ============================================

/**
 * Set a session value
 */
function session_set($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * Get a session value with default
 */
function session_get($key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

/**
 * Check if session key exists
 */
function session_has($key) {
    return isset($_SESSION[$key]);
}

/**
 * Remove a session key
 */
function session_forget($key) {
    unset($_SESSION[$key]);
}

/**
 * Destroy entire session
 */
function session_destroy_all() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}

// ============================================
// FLASH MESSAGES
// ============================================

/**
 * Set a flash message (shown once on next page load)
 * Types: success | danger | warning | info
 */
function flash_set($type, $message) {
    $_SESSION['_flash'][] = [
        'type'    => $type,
        'message' => $message
    ];
}

/**
 * Get and clear all flash messages
 */
function flash_get_all() {
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $messages;
}

/**
 * Check if any flash messages exist
 */
function flash_has() {
    return !empty($_SESSION['_flash']);
}

// ============================================
// CSRF PROTECTION
// ============================================

/**
 * Get (or generate) CSRF token for this session
 */
function csrf_token() {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

/**
 * Output hidden CSRF input field for forms
 */
function csrf_field() {
    $token = csrf_token();
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify a submitted CSRF token
 */
function csrf_verify($token = null) {
    $token = $token ?? ($_POST['_csrf'] ?? '');
    if (empty($token) || empty($_SESSION['_csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['_csrf_token'], $token);
}

/**
 * Hard-fail if CSRF token is invalid (for POST handlers)
 */
function csrf_require() {
    if (!csrf_verify()) {
        http_response_code(419);
        die('Security token expired. Please refresh the page and try again.');
    }
}
?>
<?php
/**
 * AgriConnect — Admin Authentication
 * Login protection, user checks, password hashing
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

// ============================================
// ADMIN TABLE (created on first run if missing)
// ============================================
function agri_auth_ensure_table() {
    static $ensured = false;
    if ($ensured) return;

    agri_query("
        CREATE TABLE IF NOT EXISTS agri_admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NULL,
            email VARCHAR(100) NULL,
            role ENUM('admin', 'editor') DEFAULT 'admin',
            is_active TINYINT(1) DEFAULT 1,
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Seed default admin if table empty
    $existing = agri_fetch_one("SELECT COUNT(*) AS c FROM agri_admins");
    if (empty($existing['c'])) {
        $defaultUser = 'admin';
        $defaultPass = password_hash('AgriConnect2026!', PASSWORD_DEFAULT);
        agri_query(
            "INSERT INTO agri_admins (username, password_hash, full_name, email)
             VALUES (?, ?, 'Default Admin', 'admin@mubeetech.com.ng')",
            [$defaultUser, $defaultPass]
        );
    }

    $ensured = true;
}

// ============================================
// LOGIN / LOGOUT
// ============================================

/**
 * Attempt to log an admin in
 * @return array ['success' => bool, 'message' => string]
 */
function auth_login($username, $password) {
    agri_auth_ensure_table();

    $username = trim($username);

    if ($username === '' || $password === '') {
        return ['success' => false, 'message' => 'Username and password are required.'];
    }

    // Basic brute-force throttle
    $attempts = $_SESSION['_login_attempts'] ?? 0;
    $lastTry  = $_SESSION['_login_last'] ?? 0;
    if ($attempts >= 5 && (time() - $lastTry) < 300) {
        $wait = 300 - (time() - $lastTry);
        return ['success' => false, 'message' => "Too many attempts. Try again in {$wait} seconds."];
    }

    $admin = agri_fetch_one(
        "SELECT * FROM agri_admins WHERE username = ? AND is_active = 1",
        [$username]
    );

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        $_SESSION['_login_attempts'] = $attempts + 1;
        $_SESSION['_login_last'] = time();
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }

    // Success — reset throttle, set session
    unset($_SESSION['_login_attempts'], $_SESSION['_login_last']);

    // Regenerate session id to prevent fixation
    session_regenerate_id(true);

    $_SESSION['admin_id']       = (int)$admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_name']     = $admin['full_name'] ?: $admin['username'];
    $_SESSION['admin_role']     = $admin['role'];
    $_SESSION['admin_login_at'] = time();

    agri_query(
        "UPDATE agri_admins SET last_login = NOW() WHERE id = ?",
        [$admin['id']]
    );

    return ['success' => true, 'message' => 'Welcome back, ' . ($admin['full_name'] ?: $admin['username'])];
}

/**
 * Log the current admin out
 */
function auth_logout() {
    session_destroy_all();
}

// ============================================
// AUTHENTICATION CHECKS
// ============================================

/**
 * Is the current visitor logged in?
 */
function auth_check() {
    return !empty($_SESSION['admin_id']);
}

/**
 * Get current admin info
 */
function auth_user() {
    if (!auth_check()) return null;
    return [
        'id'       => $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username'] ?? '',
        'name'     => $_SESSION['admin_name'] ?? '',
        'role'     => $_SESSION['admin_role'] ?? 'admin',
    ];
}

/**
 * Get current admin id
 */
function auth_id() {
    return $_SESSION['admin_id'] ?? null;
}

/**
 * Is the current user an admin (not just editor)?
 */
function auth_is_admin() {
    return ($_SESSION['admin_role'] ?? '') === 'admin';
}

// ============================================
// GUARDS
// ============================================

/**
 * Require the visitor to be logged in.
 * Redirects to login page if not.
 */
function auth_require_login() {
    if (!auth_check()) {
        // Detect if we're inside /admin/
        $isAdminDir = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') !== false;
        $loginUrl = $isAdminDir ? 'login.php' : '/admin/login.php';

        // Preserve intended destination
        $intended = $_SERVER['REQUEST_URI'] ?? '';
        if ($intended) {
            $_SESSION['_intended_url'] = $intended;
        }

        header('Location: ' . $loginUrl);
        exit;
    }

    // Session timeout (2 hours idle)
    $last = $_SESSION['admin_login_at'] ?? 0;
    if ($last && (time() - $last) > 7200) {
        auth_logout();
        flash_set('warning', 'Your session expired. Please log in again.');
        header('Location: login.php');
        exit;
    }

    // Refresh activity timestamp
    $_SESSION['admin_login_at'] = time();
}

/**
 * Require the visitor to be a full admin (not editor).
 */
function auth_require_admin() {
    auth_require_login();
    if (!auth_is_admin()) {
        http_response_code(403);
        die('Access denied. Admin privileges required.');
    }
}

/**
 * Get and clear the intended URL after login.
 */
function auth_pull_intended() {
    $url = $_SESSION['_intended_url'] ?? null;
    unset($_SESSION['_intended_url']);
    return $url;
}

// ============================================
// ADMIN USER MANAGEMENT
// ============================================

/**
 * Create a new admin user
 */
function auth_create_admin($username, $password, $fullName = '', $email = '', $role = 'admin') {
    agri_auth_ensure_table();

    $username = trim($username);
    if ($username === '' || strlen($password) < 8) {
        return ['success' => false, 'message' => 'Username required and password must be at least 8 characters.'];
    }

    $existing = agri_fetch_one(
        "SELECT id FROM agri_admins WHERE username = ?",
        [$username]
    );
    if ($existing) {
        return ['success' => false, 'message' => 'Username already exists.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    agri_query(
        "INSERT INTO agri_admins (username, password_hash, full_name, email, role)
         VALUES (?, ?, ?, ?, ?)",
        [$username, $hash, $fullName, $email, $role]
    );

    return ['success' => true, 'message' => 'Admin user created.'];
}

/**
 * Update password for a user
 */
function auth_update_password($userId, $newPassword) {
    if (strlen($newPassword) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
    }
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    agri_query(
        "UPDATE agri_admins SET password_hash = ? WHERE id = ?",
        [$hash, (int)$userId]
    );
    return ['success' => true, 'message' => 'Password updated.'];
}
?>
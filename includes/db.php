<?php
/**
 * AgriConnect Database Layer
 *
 * Resolution order for credentials:
 *   1. Environment variables (Docker / Marketplace deployments)
 *   2. cPanel / shared hosting defaults (hardcoded below)
 *
 * For Docker: set DB_HOST, DB_NAME, DB_USER, DB_PASS env vars.
 * For cPanel: leave env vars unset — the defaults kick in.
 */

require_once __DIR__ . '/config.php';

// ============================================
// DATABASE CREDENTIALS
// ============================================
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: '*********';
$db_user = getenv('DB_USER') ?: '*********';
$db_pass = getenv('DB_PASS') ?: '*********';
$db_port = (int)(getenv('DB_PORT') ?: 3306);

// ============================================
// CONNECT
// ============================================
$conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$conn) {
    error_log('AgriConnect DB Connection Failed: ' . mysqli_connect_error());

    // For USSD, return a safe error
    if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'ussd.php') {
        header('Content-Type: text/plain');
        echo "END Service temporarily unavailable. Please try again later.";
        exit;
    }
    die('Database connection failed.');
}

mysqli_set_charset($conn, 'utf8mb4');

// ============================================
// AUTO-PROVISIONING (for Docker/Marketplace)
// ============================================
// When the container boots with an empty DB, run our schema + seeds once.
// Safe on cPanel because CREATE TABLE IF NOT EXISTS + INSERT IGNORE.
$__schema_file = dirname(__DIR__) . '/database/agriconnect.sql';
if (file_exists($__schema_file)) {
    $__marker = sys_get_temp_dir() . '/agriconnect_schema_applied';
    if (!file_exists($__marker)) {
        // Check if our core table exists; if not, apply the schema
        $check = @mysqli_query($conn, "SHOW TABLES LIKE 'agri_farmers'");
        $needsSetup = (!$check || mysqli_num_rows($check) === 0);

        if ($needsSetup) {
            $sql = file_get_contents($__schema_file);
            if ($sql) {
                // mysqli::multi_query handles multi-statement SQL
                if (@mysqli_multi_query($conn, $sql)) {
                    // Consume all result sets
                    while (mysqli_more_results($conn) && mysqli_next_result($conn)) {
                        // drain
                    }
                    error_log('AgriConnect: schema applied from database/agriconnect.sql');
                } else {
                    error_log('AgriConnect: schema apply failed — ' . mysqli_error($conn));
                }
            }
        }

        // Touch the marker so we don't re-run on every request
        @file_put_contents($__marker, date('c'));
    }
    unset($__marker);
}
unset($__schema_file);

// ============================================
// QUERY HELPERS
// ============================================

/**
 * Run a query, with optional prepared params
 */
function agri_query($sql, $params = []) {
    global $conn;

    if (empty($params)) {
        $result = $conn->query($sql);
        if ($result === false) {
            error_log('AgriQuery Error: ' . $conn->error . ' | SQL: ' . $sql);
        }
        return $result;
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('AgriPrepare Error: ' . $conn->error . ' | SQL: ' . $sql);
        return false;
    }

    $types = '';
    foreach ($params as $p) {
        if (is_int($p))       $types .= 'i';
        elseif (is_float($p)) $types .= 'd';
        else                  $types .= 's';
    }

    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        error_log('AgriExecute Error: ' . $stmt->error . ' | SQL: ' . $sql);
        return false;
    }

    return $stmt->get_result();
}

/**
 * Fetch a single row as associative array
 */
function agri_fetch_one($sql, $params = []) {
    $result = agri_query($sql, $params);
    if (!$result) return null;
    return $result->fetch_assoc();
}

/**
 * Fetch all rows as associative array
 */
function agri_fetch_all($sql, $params = []) {
    $result = agri_query($sql, $params);
    if (!$result) return [];
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

/**
 * Get last insert ID
 */
function agri_last_id() {
    global $conn;
    return $conn->insert_id;
}
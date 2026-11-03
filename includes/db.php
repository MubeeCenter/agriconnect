<?php
/**
 * AgriConnect Database Layer
 * Uses the existing mubeetec_portfolio_db with agri_ prefixed tables
 */

require_once __DIR__ . '/config.php';

// ============================================
// DATABASE CREDENTIALS
// ============================================
$db_host = 'localhost';
$db_name = 'mubeetec_portfolio_db';
$db_user = 'mubeetec_mubee';
$db_pass = 'Mubee2019@';

// ============================================
// CONNECT
// ============================================
$conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);

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
// QUERY HELPERS
// ============================================

/**
 * Run a query, with optional prepared params
 * @param string $sql
 * @param array  $params
 * @return mysqli_result|bool
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
        if (is_int($p))      $types .= 'i';
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
?>
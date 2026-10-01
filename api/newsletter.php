<?php
/**
 * AgriConnect — Newsletter Signup Endpoint
 * Accepts POST: phone
 * Returns JSON: { success: bool, message: string }
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/validation.php';

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// CSRF check (JS sends the token from the form)
if (!csrf_verify()) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Session expired. Please refresh and try again.']);
    exit;
}

$phone = sanitize_phone($_POST['phone'] ?? '');

if (!v_phone_ng($phone)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid Nigerian phone number.']);
    exit;
}

// Ensure farmer exists
ensureFarmerExists($phone);

// Enable alerts
enableFarmerAlerts($phone);

// Send welcome SMS
if (file_exists(__DIR__ . '/../includes/at-sms.php')) {
    require_once __DIR__ . '/../includes/at-sms.php';
    @sendSMS($phone,
        "AgriConnect: Welcome! You'll now receive daily market prices for Kano, Kaduna, and Abuja. Reply STOP to unsubscribe. Dial " . AT_USSD_CODE . " to get started.");
}

// Log the signup
agri_log($phone, 'newsletter_signup', 'Subscribed via website form');

echo json_encode([
    'success' => true,
    'message' => 'Subscribed! You will receive SMS price alerts.'
]);
<?php
/**
 * Admin Logout
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

auth_logout();

session_start();
flash_set('success', 'You have been logged out.');
header('Location: login.php');
exit;
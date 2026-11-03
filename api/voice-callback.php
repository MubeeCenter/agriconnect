<?php
/**
 * AgriConnect — Voice API Callback
 * Africa's Talking calls this endpoint after a voice call is initiated.
 * We must respond with Voice XML that controls the call flow.
 *
 * Flow:
 *   1. Call is initiated to the FARMER (from voice-queue "process" action)
 *   2. AT hits this endpoint with ?queue_id=<id>
 *   3. We say a greeting, then Dial the dealer's number to bridge the call
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/at-voice.php';

header('Content-Type: application/xml; charset=UTF-8');

// Africa's Talking may send POST data with call info
$queueId = (int)($_GET['queue_id'] ?? $_POST['queue_id'] ?? 0);
$sessionId = $_POST['sessionId'] ?? $_GET['sessionId'] ?? '';
$callerNumber = $_POST['callerNumber'] ?? $_GET['callerNumber'] ?? '';

// Log every hit
log_write('voice', "Callback | queue_id=$queueId | session=$sessionId | caller=$callerNumber");

// If we don't have a queue entry, respond with a graceful error and hang up
if ($queueId <= 0) {
    echo voice_xml(voice_say('Sorry, this call could not be completed. Please try again.'));
    exit;
}

// Fetch the queue entry
$entry = agri_fetch_one("SELECT * FROM agri_voice_queue WHERE id = ?", [$queueId]);

if (!$entry) {
    echo voice_xml(voice_say('Sorry, this request was not found. Please try again.'));
    exit;
}

// Already processed? Don't re-bridge.
if ($entry['status'] !== 'pending' && $entry['status'] !== 'connected') {
    echo voice_xml(voice_say('This call has already been completed. Thank you.'));
    exit;
}

// Mark as connected (in-progress)
agri_query("UPDATE agri_voice_queue SET status = 'connected' WHERE id = ?", [$queueId]);

// Build the Voice XML
$dealerNumber = $entry['dealer_phone'];

$body = '';
$body .= voice_say('Welcome to AgriConnect. Connecting you to the agro-dealer now. Please hold.');
$body .= voice_dial($dealerNumber, true); // record=true for audit
$body .= voice_say('The call has ended. Thank you for using AgriConnect. Goodbye.');

// Mark as completed when call finishes
agri_query(
    "UPDATE agri_voice_queue SET status = 'completed', completed_at = NOW() WHERE id = ?",
    [$queueId]
);

echo voice_xml($body);
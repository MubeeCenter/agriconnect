<?php
/**
 * AgriConnect USSD Handler
 * Endpoint: https://biz.mubeetech.com.ng/api/ussd.php
 * Shortcode: *384*0032#
 */

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

$baseDir = dirname(__DIR__);
if (!is_dir($baseDir . '/logs')) {
    @mkdir($baseDir . '/logs', 0755, true);
}
ini_set('error_log', $baseDir . '/logs/error.log');

// ============================================
// BOOTSTRAP — NOTE: at-voice.php is now included
// because queue_voice_call() lives there.
// ============================================
$required = ['config.php', 'db.php', 'functions.php', 'at-sms.php', 'at-voice.php'];
foreach ($required as $file) {
    if (!file_exists($baseDir . '/includes/' . $file)) {
        header('Content-Type: text/plain');
        echo "END Service configuration error [$file]. Contact support.";
        exit;
    }
}

require_once $baseDir . '/includes/config.php';
require_once $baseDir . '/includes/db.php';
require_once $baseDir . '/includes/functions.php';
require_once $baseDir . '/includes/at-sms.php';
require_once $baseDir . '/includes/at-voice.php';

header('Content-Type: text/plain');

$sessionId   = $_POST['sessionId']   ?? '';
$serviceCode = $_POST['serviceCode'] ?? '';
$phoneNumber = $_POST['phoneNumber'] ?? '';
$text        = trim($_POST['text'] ?? '');

$input = ($text === '') ? [] : explode('*', $text);
$level = count($input);

log_ussd($phoneNumber, $text, $level);

if (!empty($phoneNumber)) {
    ensureFarmerExists($phoneNumber);
}

$response = '';

try {
    // ============================================
    // LEVEL 0: Main Menu
    // ============================================
    if ($text === '') {
        $response = mainMenu();
    }

    // ============================================
    // LEVEL 1: Main menu selection
    // ============================================
    elseif ($level === 1) {
        switch ($input[0]) {
            case '1':
                $response = cropMenu();
                break;
            case '2':
                $response = marketMenu("Select Market for Dealers");
                break;
            case '3':
                $response = "CON Report Pest/Disease\n" .
                            "Send an SMS with details to 384#.\n" .
                            "Our team will respond within 24 hours.\n\n" .
                            "0. Main Menu";
                break;
            case '4':
                $farmer = getFarmer($phoneNumber);
                $alerts = !empty($farmer['sms_alerts_enabled']) ? 'ON' : 'OFF';
                $response = "END Your Account\n" .
                            "Phone: $phoneNumber\n" .
                            "Region: " . ($farmer['state'] ?? 'Unknown') . "\n" .
                            "SMS Alerts: $alerts\n\n" .
                            "Thank you for using AgriConnect.";
                break;
            default:
                $response = "END Invalid choice. Dial " . AT_USSD_CODE . " to try again.";
        }
    }

    // ============================================
    // LEVEL 2
    // ============================================
    elseif ($level === 2) {
        // --- Price path: 1 → crop ---
        if ($input[0] === '1') {
            if ($input[1] === '0') {
                $response = mainMenu();
            } else {
                $crop = mapCrop($input[1]);
                if (!$crop) {
                    $response = "END Invalid crop. Dial " . AT_USSD_CODE . " to try again.";
                } else {
                    $response = marketMenu("Select Market for $crop");
                }
            }
        }
        // --- Dealer path: 2 → market ---
        elseif ($input[0] === '2') {
            if ($input[1] === '0') {
                $response = mainMenu();
            } else {
                $market = mapMarket($input[1]);
                if (!$market) {
                    $response = "END Invalid market. Dial " . AT_USSD_CODE . " to try again.";
                } else {
                    $response = buildDealerMenu($market);
                }
            }
        }
        // --- Pest report back ---
        elseif ($input[0] === '3' && $input[1] === '0') {
            $response = mainMenu();
        }
        else {
            $response = "END Session error. Dial " . AT_USSD_CODE . " to restart.";
        }
    }

    // ============================================
    // LEVEL 3
    //   A) Price path: 1*crop*market → show price
    //   B) Dealer path: 2*market*dealerId → show dealer details
    // ============================================
    elseif ($level === 3) {
        // --- Price path ---
        if ($input[0] === '1') {
            $crop   = mapCrop($input[1]);
            $market = mapMarket($input[2]);

            if (!$crop || !$market) {
                $response = "END Invalid selection. Dial " . AT_USSD_CODE . " to restart.";
            } else {
                setFarmerState($phoneNumber, $market);
                $listing = getLatestPrice($crop, $market);

                if ($listing) {
                    $price    = formatNaira($listing['price']);
                    $verified = $listing['verification_count'] >= USSD_VERIFY_THRESHOLD
                        ? "YES ({$listing['verification_count']} farmers)"
                        : "PENDING ({$listing['verification_count']}/" . USSD_VERIFY_THRESHOLD . ")";
                    $updated  = timeAgo($listing['updated_at']);

                    $alreadyVerified = hasUserVerifiedListing((int)$listing['id'], $phoneNumber);

                    $response  = "CON $crop - $market\n";
                    $response .= "Price: $price/ton\n";
                    $response .= "Verified: $verified\n";
                    $response .= "Updated: $updated\n\n";
                    $response .= "1. Find Dealer\n";
                    $response .= "2. Get SMS Alerts\n";
                    if (!$alreadyVerified) {
                        $response .= "3. Confirm this Price\n";
                    }
                    $response .= "0. Main Menu";
                } else {
                    $response = "CON No price data for $crop in $market.\n\n" .
                                "1. Submit Price\n" .
                                "0. Main Menu";
                }
            }
        }
        // --- Dealer path ---
        elseif ($input[0] === '2') {
            $dealerId = $input[2] ?? null;
            if ($dealerId && $dealerId !== '0') {
                $dealer = getDealerById($dealerId);
                if ($dealer) {
                    $response = "CON {$dealer['business_name']}\n" .
                                "Phone: {$dealer['phone']}\n" .
                                "Products: {$dealer['products']}\n\n" .
                                "1. Request Voice Call\n" .
                                "2. Send SMS Inquiry\n" .
                                "0. Back";
                } else {
                    $response = "END Dealer not found. Dial " . AT_USSD_CODE . " to try again.";
                }
            } else {
                $response = mainMenu();
            }
        }
        else {
            $response = "END Session error. Dial " . AT_USSD_CODE . " to restart.";
        }
    }

    // ============================================
    // LEVEL 4
    //   A) Price path action: 1*crop*market*action
    //   B) Dealer path action: 2*market*dealerId*action
    // ============================================
    elseif ($level === 4) {
        // --- Price path actions ---
        if ($input[0] === '1' && $input[3] === '1') {
            $market = mapMarket($input[2]);
            $response = buildDealerMenu($market);
        }
        elseif ($input[0] === '1' && $input[3] === '2') {
            enableFarmerAlerts($phoneNumber);
            sendSMS(
                $phoneNumber,
                "AgriConnect: SMS alerts activated. You will receive daily price updates for your region. Reply STOP to unsubscribe."
            );
            $response = "END SMS alerts activated.\n" .
                        "You will receive daily price updates.\n\n" .
                        "Thank you for using AgriConnect.";
        }
        elseif ($input[0] === '1' && $input[3] === '3') {
            $crop   = mapCrop($input[1]);
            $market = mapMarket($input[2]);
            $listing = getLatestPrice($crop, $market);

            if (!$listing) {
                $response = "END Listing not found. Dial " . AT_USSD_CODE . " to try again.";
            } else {
                $result = recordVerification((int)$listing['id'], $phoneNumber);
                agri_log($phoneNumber, 'ussd_verify',
                    "listing_id={$listing['id']} source=ussd ok=" . ($result['success'] ? '1' : '0'));

                $listing = getListingById((int)$listing['id']);
                $count   = (int)($listing['verification_count'] ?? 0);
                $isVer   = !empty($listing['is_verified']);

                if ($result['success']) {
                    $statusLine = $isVer
                        ? "Status: VERIFIED ✓"
                        : "Status: PENDING ($count/" . USSD_VERIFY_THRESHOLD . ")";

                    $response = "END Thank you! Your confirmation has been recorded.\n\n" .
                                "$crop - $market\n" .
                                "$statusLine\n\n" .
                                "Dial " . AT_USSD_CODE . " to check again.";
                } else {
                    $response = "END " . $result['message'] . "\n\n" .
                                "Dial " . AT_USSD_CODE . " to continue.";
                }
            }
        }
        elseif ($input[0] === '1' && $input[3] === '0') {
            $response = mainMenu();
        }
        // --- Dealer path actions: 2*market*dealerId*action ---
        elseif ($input[0] === '2') {
            $dealerId = $input[2] ?? null;
            $action   = $input[3] ?? null;

            if ($dealerId && $action) {
                $dealer = getDealerById($dealerId);

                if (!$dealer) {
                    $response = "END Dealer not found. Dial " . AT_USSD_CODE . " to try again.";
                }
                elseif ($action === '1') {
                    queue_voice_call($phoneNumber, $dealer['phone']);
                    sendSMS(
                        $dealer['phone'],
                        "AgriConnect: Farmer {$phoneNumber} requested a callback. Please call them back."
                    );
                    $response = "END Request received.\n" .
                                "{$dealer['business_name']} will call you shortly.\n\n" .
                                "Thank you for using AgriConnect.";
                }
                elseif ($action === '2') {
                    sendSMS(
                        $dealer['phone'],
                        "AgriConnect Inquiry: Farmer at {$phoneNumber} is interested in your products. Please call them back."
                    );
                    sendSMS(
                        $phoneNumber,
                        "Your inquiry has been sent to {$dealer['business_name']}. They will contact you shortly."
                    );
                    $response = "END SMS inquiry sent.\n" .
                                "Dealer will contact you shortly.\n\n" .
                                "Thank you for using AgriConnect.";
                }
                else {
                    $response = mainMenu();
                }
            } else {
                $response = mainMenu();
            }
        }
        else {
            $response = "END Session error. Dial " . AT_USSD_CODE . " to restart.";
        }
    }

    // ============================================
    // LEVEL 5: Price path → Find Dealer → dealer detail
    // ============================================
    elseif ($level === 5) {
        $dealerId = null;

        if ($input[0] === '1' && ($input[3] ?? '') === '1') {
            $dealerId = $input[4] ?? null;
        }

        if ($dealerId && $dealerId !== '0') {
            $dealer = getDealerById($dealerId);
            if ($dealer) {
                $response = "CON {$dealer['business_name']}\n" .
                            "Phone: {$dealer['phone']}\n" .
                            "Products: {$dealer['products']}\n\n" .
                            "1. Request Voice Call\n" .
                            "2. Send SMS Inquiry\n" .
                            "0. Back";
            } else {
                $response = "END Dealer not found. Dial " . AT_USSD_CODE . " to try again.";
            }
        } else {
            $response = mainMenu();
        }
    }

    // ============================================
    // LEVEL 6: Price path → dealer action
    // ============================================
    elseif ($level === 6) {
        $dealerId = $input[4] ?? null;
        $action   = $input[5] ?? null;

        if ($dealerId && $action) {
            $dealer = getDealerById($dealerId);

            if (!$dealer) {
                $response = "END Dealer not found. Dial " . AT_USSD_CODE . " to try again.";
            }
            elseif ($action === '1') {
                queue_voice_call($phoneNumber, $dealer['phone']);
                sendSMS(
                    $dealer['phone'],
                    "AgriConnect: Farmer {$phoneNumber} requested a callback. Please call them back."
                );
                $response = "END Request received.\n" .
                            "{$dealer['business_name']} will call you shortly.\n\n" .
                            "Thank you for using AgriConnect.";
            }
            elseif ($action === '2') {
                sendSMS(
                    $dealer['phone'],
                    "AgriConnect Inquiry: Farmer at {$phoneNumber} is interested in your products. Please call them back."
                );
                sendSMS(
                    $phoneNumber,
                    "Your inquiry has been sent to {$dealer['business_name']}. They will contact you shortly."
                );
                $response = "END SMS inquiry sent.\n" .
                            "Dealer will contact you shortly.\n\n" .
                            "Thank you for using AgriConnect.";
            }
            else {
                $response = mainMenu();
            }
        } else {
            $response = mainMenu();
        }
    }

    else {
        $response = "END Session ended. Dial " . AT_USSD_CODE . " to start again.";
    }
}
catch (Throwable $e) {
    error_log('USSD Exception: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
    $response = "END Service error. Please try again. Dial " . AT_USSD_CODE . ".";
}

echo $response;
exit;

// ============================================
// LOCAL MENU BUILDER
// ============================================
function buildDealerMenu($market) {
    $dealers = getDealersByMarket($market, MAX_DEALERS_PER_MENU);

    if (empty($dealers)) {
        return "CON No verified dealers in $market yet.\n\n0. Main Menu";
    }

    $menu = "CON Agro-Dealers in $market:\n";
    foreach ($dealers as $d) {
        $menu .= "{$d['id']}. {$d['business_name']}\n";
    }
    $menu .= "0. Main Menu";
    return $menu;
}
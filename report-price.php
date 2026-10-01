<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/validation.php';

$pageTitle       = 'Report a Price';
$pageDescription = 'Submit a market price you observed. Help other farmers get accurate data.';
$currentPage     = 'prices';

$success = '';
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $result = validate_form($_POST, [
        'crop'   => ['label' => 'Crop',   'rules' => 'required|in:Maize,Cowpea,Tomato'],
        'market' => ['label' => 'Market', 'rules' => 'required|in:Kano,Kaduna,Abuja'],
        'price'  => ['label' => 'Price',  'rules' => 'required|number|min_value:100|max_value:10000000', 'sanitize' => 'float'],
        'phone'  => ['label' => 'Phone',  'rules' => 'required|phone', 'sanitize' => 'phone'],
    ]);

    if ($result['valid']) {
        $d = $result['data'];
        agri_query(
            "INSERT INTO agri_listings (crop, market, price, submitted_by, source, verification_count, is_verified)
             VALUES (?, ?, ?, ?, 'web', 1, 0)",
            [$d['crop'], $d['market'], $d['price'], $d['phone']]
        );

        // Record first verification
        $newId = agri_last_id();
        agri_query(
            "INSERT INTO agri_verifications (listing_id, phone) VALUES (?, ?)",
            [$newId, $d['phone']]
        );

        // Send confirmation SMS
        if (function_exists('sendSMS')) {
            @sendSMS($d['phone'],
                "AgriConnect: Thanks for reporting {$d['crop']} at " . formatNaira($d['price']) . " in {$d['market']}. 2 more verifications will mark this price as verified.");
        }

        $success = 'Thank you! Your price has been submitted.';
    } else {
        $errors = $result['errors'];
        errors_set($errors);
    }
}

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$heroEyebrow  = 'Community Powered';
$heroTitle    = 'Report a Market Price';
$heroSubtitle = 'Help other farmers by sharing the price you observed.';
$heroCompact  = true;
$heroActions  = [];
require __DIR__ . '/partials/hero.php';
?>

<section class="agri-section">
    <div class="agri-container agri-container-narrow">

        <?php if ($success): ?>
            <div class="agri-alert agri-alert-success" data-auto-dismiss="6000">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="agri-alert agri-alert-danger">
                Please fix the errors below and try again.
            </div>
        <?php endif; ?>

        <div class="agri-card">
            <h3>Price Report Form</h3>
            <p class="agri-text-muted">
                Only submit prices you have <strong>personally confirmed</strong> at the market.
            </p>

            <form method="post" data-validate-form>
                <?= csrf_field() ?>

                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Crop</label>
                    <select name="crop" class="agri-select" required>
                        <option value="">— Select crop —</option>
                        <option value="Maize">Maize</option>
                        <option value="Cowpea">Cowpea</option>
                        <option value="Tomato">Tomato</option>
                    </select>
                    <?php if (!empty($errors['crop'])): ?>
                        <div class="agri-form-error"><?= e($errors['crop']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Market</label>
                    <select name="market" class="agri-select" required>
                        <option value="">— Select market —</option>
                        <option value="Kano">Kano</option>
                        <option value="Kaduna">Kaduna</option>
                        <option value="Abuja">Abuja</option>
                    </select>
                    <?php if (!empty($errors['market'])): ?>
                        <div class="agri-form-error"><?= e($errors['market']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Price (₦ per ton)</label>
                    <input type="text" name="price" class="agri-input" placeholder="45000"
                           required data-format-number>
                    <?php if (!empty($errors['price'])): ?>
                        <div class="agri-form-error"><?= e($errors['price']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Your Phone Number</label>
                    <input type="tel" name="phone" class="agri-input" placeholder="08012345678"
                           required data-format-phone>
                    <div class="agri-form-hint">We'll send you a confirmation SMS.</div>
                    <?php if (!empty($errors['phone'])): ?>
                        <div class="agri-form-error"><?= e($errors['phone']) ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="agri-btn agri-btn-primary agri-btn-lg">
                    Submit Price
                </button>
            </form>
        </div>

        <div class="agri-alert agri-alert-info agri-mt-6">
            <strong>Note:</strong> All price submissions are verified before being marked as "Verified".
            A price needs 3+ independent submissions to earn the Verified badge.
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php';
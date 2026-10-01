<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/validation.php';
agri_boot_admin();

$pageTitle    = 'Add Listing';
$pageSubtitle = 'Create a new market price';
$currentPage  = 'listings';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $result = validate_form($_POST, [
        'crop'   => ['label' => 'Crop',   'rules' => 'required|in:Maize,Cowpea,Tomato'],
        'market' => ['label' => 'Market', 'rules' => 'required|in:Kano,Kaduna,Abuja'],
        'price'  => ['label' => 'Price',  'rules' => 'required|number|min_value:100', 'sanitize' => 'float'],
        'status' => ['label' => 'Status', 'rules' => 'required|in:0,1'],
    ]);

    if ($result['valid']) {
        $d = $result['data'];
        agri_query(
            "INSERT INTO agri_listings (crop, market, price, is_verified, verification_count, source)
             VALUES (?, ?, ?, ?, ?, 'admin')",
            [$d['crop'], $d['market'], $d['price'], (int)$d['status'],
             (int)$d['status'] ? USSD_VERIFY_THRESHOLD : 0]
        );
        flash_set('success', 'Listing created successfully.');
        header('Location: listings.php');
        exit;
    }
    $errors = $result['errors'];
}

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require __DIR__ . '/partials/topbar.php';
?>

<div class="admin-content">

    <div class="admin-page-header">
        <div>
            <h2>Add Listing</h2>
            <p class="agri-text-muted agri-mb-0">Create a new market price for farmers</p>
        </div>
        <div class="admin-page-actions">
            <a href="listings.php" class="agri-btn agri-btn-ghost agri-btn-sm">← Back</a>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="agri-alert agri-alert-danger">Please fix the errors below.</div>
    <?php endif; ?>

    <div class="admin-card" style="max-width:640px;">
        <div class="admin-card-body">
            <form method="post" data-validate-form>
                <?= csrf_field() ?>

                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Crop</label>
                    <select name="crop" class="agri-select" required>
                        <option value="">— Select —</option>
                        <?php foreach (['Maize','Cowpea','Tomato'] as $c): ?>
                            <option value="<?= $c ?>" <?= ($_POST['crop'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['crop'])): ?><div class="agri-form-error"><?= e($errors['crop']) ?></div><?php endif; ?>
                </div>

                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Market</label>
                    <select name="market" class="agri-select" required>
                        <option value="">— Select —</option>
                        <?php foreach (['Kano','Kaduna','Abuja'] as $m): ?>
                            <option value="<?= $m ?>" <?= ($_POST['market'] ?? '') === $m ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['market'])): ?><div class="agri-form-error"><?= e($errors['market']) ?></div><?php endif; ?>
                </div>

                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Price (₦ per ton)</label>
                    <input type="text" name="price" class="agri-input"
                           value="<?= e($_POST['price'] ?? '') ?>"
                           required data-format-number placeholder="45000">
                    <?php if (!empty($errors['price'])): ?><div class="agri-form-error"><?= e($errors['price']) ?></div><?php endif; ?>
                </div>

                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Status</label>
                    <select name="status" class="agri-select" required>
                        <option value="0" <?= ($_POST['status'] ?? '0') === '0' ? 'selected' : '' ?>>Pending (needs verification)</option>
                        <option value="1" <?= ($_POST['status'] ?? '') === '1' ? 'selected' : '' ?>>Verified (skip verification)</option>
                    </select>
                </div>

                <div class="agri-flex" style="gap:12px;margin-top:24px;">
                    <button type="submit" class="agri-btn agri-btn-primary">Save Listing</button>
                    <a href="listings.php" class="agri-btn agri-btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
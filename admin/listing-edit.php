<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/validation.php';
agri_boot_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: listings.php'); exit; }

$listing = agri_fetch_one("SELECT * FROM agri_listings WHERE id = ?", [$id]);
if (!$listing) { header('Location: listings.php'); exit; }

$pageTitle    = 'Edit Listing';
$pageSubtitle = $listing['crop'] . ' · ' . $listing['market'];
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
            "UPDATE agri_listings SET crop = ?, market = ?, price = ?, is_verified = ? WHERE id = ?",
            [$d['crop'], $d['market'], $d['price'], (int)$d['status'], $id]
        );
        flash_set('success', 'Listing updated.');
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
            <h2>Edit Listing</h2>
            <p class="agri-text-muted agri-mb-0">ID #<?= (int)$listing['id'] ?></p>
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
                        <?php foreach (['Maize','Cowpea','Tomato'] as $c): ?>
                            <option value="<?= $c ?>" <?= $listing['crop'] === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Market</label>
                    <select name="market" class="agri-select" required>
                        <?php foreach (['Kano','Kaduna','Abuja'] as $m): ?>
                            <option value="<?= $m ?>" <?= $listing['market'] === $m ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Price (₦ per ton)</label>
                    <input type="text" name="price" class="agri-input"
                           value="<?= e($listing['price']) ?>" required data-format-number>
                </div>

                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Status</label>
                    <select name="status" class="agri-select" required>
                        <option value="0" <?= !$listing['is_verified'] ? 'selected' : '' ?>>Pending</option>
                        <option value="1" <?= $listing['is_verified'] ? 'selected' : '' ?>>Verified</option>
                    </select>
                </div>

                <div class="agri-alert agri-alert-info">
                    <strong>Verifications:</strong> <?= (int)$listing['verification_count'] ?>
                    &nbsp;·&nbsp;
                    <a href="verifications.php?listing_id=<?= (int)$listing['id'] ?>">View history →</a>
                </div>

                <div class="agri-flex" style="gap:12px;margin-top:24px;">
                    <button type="submit" class="agri-btn agri-btn-primary">Update Listing</button>
                    <a href="listings.php" class="agri-btn agri-btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/validation.php';
agri_boot_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: dealers.php'); exit; }

$dealer = agri_fetch_one("SELECT * FROM agri_dealers WHERE id = ?", [$id]);
if (!$dealer) { header('Location: dealers.php'); exit; }

$pageTitle    = 'Edit Dealer';
$pageSubtitle = $dealer['business_name'];
$currentPage  = 'dealers';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $result = validate_form($_POST, [
        'business_name' => ['label' => 'Business Name', 'rules' => 'required|min:2|max:150'],
        'phone'         => ['label' => 'Phone',         'rules' => 'required|phone', 'sanitize' => 'phone'],
        'email'         => ['label' => 'Email',         'rules' => 'email', 'sanitize' => 'email'],
        'lga'           => ['label' => 'LGA',           'rules' => 'required|max:100'],
        'state'         => ['label' => 'State',         'rules' => 'required|in:Kano,Kaduna,Abuja'],
        'products'      => ['label' => 'Products',      'rules' => 'required|max:500'],
        'address'       => ['label' => 'Address',       'rules' => 'max:500'],
        'is_verified'   => ['label' => 'Status',        'rules' => 'in:0,1'],
    ]);

    if ($result['valid']) {
        $d = $result['data'];
        agri_query(
            "UPDATE agri_dealers
             SET business_name = ?, phone = ?, email = ?, lga = ?, state = ?, products = ?, address = ?, is_verified = ?
             WHERE id = ?",
            [$d['business_name'], $d['phone'], $d['email'], $d['lga'], $d['state'],
             $d['products'], $d['address'], (int)($d['is_verified'] ?? 0), $id]
        );
        flash_set('success', 'Dealer updated.');
        header('Location: dealers.php');
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
            <h2>Edit Dealer</h2>
            <p class="agri-text-muted agri-mb-0">ID #<?= (int)$dealer['id'] ?></p>
        </div>
        <div class="admin-page-actions">
            <a href="dealers.php" class="agri-btn agri-btn-ghost agri-btn-sm">← Back</a>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="agri-alert agri-alert-danger">Please fix the errors below.</div>
    <?php endif; ?>

    <div class="admin-card" style="max-width:720px;">
        <div class="admin-card-body">
            <form method="post" data-validate-form>
                <?= csrf_field() ?>

                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Business Name</label>
                    <input type="text" name="business_name" class="agri-input" required maxlength="150"
                           value="<?= e($dealer['business_name']) ?>">
                </div>

                <div class="agri-grid agri-grid-2">
                    <div class="agri-form-group">
                        <label class="agri-label agri-label-required">Phone</label>
                        <input type="tel" name="phone" class="agri-input" required data-format-phone
                               value="<?= e($dealer['phone']) ?>">
                    </div>
                    <div class="agri-form-group">
                        <label class="agri-label">Email</label>
                        <input type="email" name="email" class="agri-input" value="<?= e($dealer['email']) ?>">
                    </div>
                </div>

                <div class="agri-grid agri-grid-2">
                    <div class="agri-form-group">
                        <label class="agri-label agri-label-required">LGA</label>
                        <input type="text" name="lga" class="agri-input" required maxlength="100"
                               value="<?= e($dealer['lga']) ?>">
                    </div>
                    <div class="agri-form-group">
                        <label class="agri-label agri-label-required">State</label>
                        <select name="state" class="agri-select" required>
                            <?php foreach (['Kano','Kaduna','Abuja'] as $s): ?>
                                <option value="<?= $s ?>" <?= $dealer['state'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Products</label>
                    <input type="text" name="products" class="agri-input" required maxlength="500"
                           value="<?= e($dealer['products']) ?>">
                </div>

                <div class="agri-form-group">
                    <label class="agri-label">Address</label>
                    <textarea name="address" class="agri-textarea" maxlength="500"><?= e($dealer['address']) ?></textarea>
                </div>

                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Status</label>
                    <select name="is_verified" class="agri-select" required>
                        <option value="0" <?= !$dealer['is_verified'] ? 'selected' : '' ?>>Pending</option>
                        <option value="1" <?= $dealer['is_verified'] ? 'selected' : '' ?>>Verified</option>
                    </select>
                </div>

                <div class="agri-flex" style="gap:12px;margin-top:24px;">
                    <button type="submit" class="agri-btn agri-btn-primary">Update Dealer</button>
                    <a href="dealers.php" class="agri-btn agri-btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
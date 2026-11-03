<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/validation.php';
agri_boot_admin();

$pageTitle    = 'Add Dealer';
$pageSubtitle = 'Register a new agro-dealer';
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
            "INSERT INTO agri_dealers (business_name, phone, email, lga, state, products, address, is_verified)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$d['business_name'], $d['phone'], $d['email'], $d['lga'], $d['state'],
             $d['products'], $d['address'], (int)($d['is_verified'] ?? 0)]
        );
        flash_set('success', 'Dealer added successfully.');
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
            <h2>Add Dealer</h2>
            <p class="agri-text-muted agri-mb-0">Register a new agro-dealer</p>
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
                           value="<?= e($_POST['business_name'] ?? '') ?>">
                    <?php if (!empty($errors['business_name'])): ?><div class="agri-form-error"><?= e($errors['business_name']) ?></div><?php endif; ?>
                </div>

                <div class="agri-grid agri-grid-2">
                    <div class="agri-form-group">
                        <label class="agri-label agri-label-required">Phone</label>
                        <input type="tel" name="phone" class="agri-input" required data-format-phone
                               value="<?= e($_POST['phone'] ?? '') ?>" placeholder="08012345678">
                        <?php if (!empty($errors['phone'])): ?><div class="agri-form-error"><?= e($errors['phone']) ?></div><?php endif; ?>
                    </div>
                    <div class="agri-form-group">
                        <label class="agri-label">Email (optional)</label>
                        <input type="email" name="email" class="agri-input"
                               value="<?= e($_POST['email'] ?? '') ?>">
                        <?php if (!empty($errors['email'])): ?><div class="agri-form-error"><?= e($errors['email']) ?></div><?php endif; ?>
                    </div>
                </div>

                <div class="agri-grid agri-grid-2">
                    <div class="agri-form-group">
                        <label class="agri-label agri-label-required">LGA</label>
                        <input type="text" name="lga" class="agri-input" required maxlength="100"
                               value="<?= e($_POST['lga'] ?? '') ?>" placeholder="Kano Municipal">
                        <?php if (!empty($errors['lga'])): ?><div class="agri-form-error"><?= e($errors['lga']) ?></div><?php endif; ?>
                    </div>
                    <div class="agri-form-group">
                        <label class="agri-label agri-label-required">State</label>
                        <select name="state" class="agri-select" required>
                            <option value="">— Select —</option>
                            <?php foreach (['Kano','Kaduna','Abuja'] as $s): ?>
                                <option value="<?= $s ?>" <?= ($_POST['state'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['state'])): ?><div class="agri-form-error"><?= e($errors['state']) ?></div><?php endif; ?>
                    </div>
                </div>

                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Products (comma-separated)</label>
                    <input type="text" name="products" class="agri-input" required maxlength="500"
                           value="<?= e($_POST['products'] ?? '') ?>" placeholder="NPK,Urea,Seeds">
                    <?php if (!empty($errors['products'])): ?><div class="agri-form-error"><?= e($errors['products']) ?></div><?php endif; ?>
                </div>

                <div class="agri-form-group">
                    <label class="agri-label">Address (optional)</label>
                    <textarea name="address" class="agri-textarea" maxlength="500"><?= e($_POST['address'] ?? '') ?></textarea>
                </div>

                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Verification Status</label>
                    <select name="is_verified" class="agri-select" required>
                        <option value="0" <?= ($_POST['is_verified'] ?? '0') === '0' ? 'selected' : '' ?>>Pending</option>
                        <option value="1" <?= ($_POST['is_verified'] ?? '') === '1' ? 'selected' : '' ?>>Verified</option>
                    </select>
                </div>

                <div class="agri-flex" style="gap:12px;margin-top:24px;">
                    <button type="submit" class="agri-btn agri-btn-primary">Save Dealer</button>
                    <a href="dealers.php" class="agri-btn agri-btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
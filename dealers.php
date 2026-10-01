<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';

$pageTitle       = 'Agro-Dealers';
$pageDescription = 'Verified agro-dealers across Northern Nigeria.';
$currentPage     = 'dealers';

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$filterState   = trim($_GET['state'] ?? '');
$filterProduct = trim($_GET['product'] ?? '');
$search        = trim($_GET['q'] ?? '');

$states = ['Kano', 'Kaduna', 'Abuja'];

$sql    = "SELECT * FROM agri_dealers WHERE is_verified = 1";
$params = [];

if ($filterState && in_array($filterState, $states, true)) {
    $sql .= " AND state = ?";
    $params[] = $filterState;
}
if ($filterProduct) {
    $sql .= " AND products LIKE ?";
    $params[] = '%' . $filterProduct . '%';
}
if ($search) {
    $sql .= " AND (business_name LIKE ? OR products LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
$sql .= " ORDER BY business_name ASC LIMIT 60";

$dealers = agri_fetch_all($sql, $params);

$heroEyebrow  = 'Trusted Network';
$heroTitle    = 'Agro-Dealers';
$heroSubtitle = 'Verified suppliers of seeds, fertilizer, and pesticides.';
$heroCompact  = true;
$heroActions  = [];
require __DIR__ . '/partials/hero.php';
?>

<section class="agri-section">
    <div class="agri-container">

        <form method="get" class="agri-card agri-mb-6" style="padding:20px;">
            <div class="agri-flex" style="gap:16px;flex-wrap:wrap;align-items:flex-end;">
                <div class="agri-form-group" style="flex:1;min-width:180px;margin:0;">
                    <label class="agri-label">Search</label>
                    <input type="text" name="q" class="agri-input" placeholder="Business name or product" value="<?= e($search) ?>">
                </div>
                <div class="agri-form-group" style="flex:0 0 160px;margin:0;">
                    <label class="agri-label">State</label>
                    <select name="state" class="agri-select">
                        <option value="">All States</option>
                        <?php foreach ($states as $s): ?>
                            <option value="<?= e($s) ?>" <?= $filterState === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="agri-btn agri-btn-primary">Search</button>
                <?php if ($search || $filterState || $filterProduct): ?>
                    <a href="<?= BASE_URL ?>/dealers.php" class="agri-btn agri-btn-ghost">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (empty($dealers)): ?>
            <div class="agri-empty">
                <div class="agri-empty-icon">🏪</div>
                <h3>No dealers found</h3>
                <p>Try a different filter or check back later.</p>
            </div>
        <?php else: ?>
            <div class="agri-grid agri-grid-3">
                <?php foreach ($dealers as $dealer): ?>
                    <div class="agri-card agri-card-hover">
                        <h4 style="margin-top:0;"><?= e($dealer['business_name']) ?></h4>
                        <p class="agri-text-sm agri-text-muted agri-mb-2">
                            📍 <?= e($dealer['lga'] . ', ' . $dealer['state']) ?>
                        </p>
                        <p class="agri-text-sm agri-mb-4">
                            <strong>Products:</strong> <?= e($dealer['products']) ?>
                        </p>
                        <div class="agri-flex-between">
                            <span class="agri-badge agri-badge-success">Verified</span>
                            <a href="<?= BASE_URL ?>/dealer.php?id=<?= (int)$dealer['id'] ?>"
                               class="agri-btn agri-btn-ghost agri-btn-sm">Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php';
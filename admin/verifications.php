<?php
require_once __DIR__ . '/../includes/functions.php';
agri_boot_admin();

$pageTitle    = 'Verifications';
$pageSubtitle = 'SMS price confirmations';
$currentPage  = 'verifications';

$listingId = (int)($_GET['listing_id'] ?? 0);
$page      = max(1, (int)($_GET['page'] ?? 1));

$where  = " WHERE 1=1";
$params = [];
if ($listingId > 0) {
    $where .= " AND v.listing_id = ?";
    $params[] = $listingId;
}

$result = paginate(
    "SELECT COUNT(*) AS c FROM agri_verifications v" . $where,
    $params,
    "SELECT v.*, l.crop, l.market FROM agri_verifications v
     LEFT JOIN agri_listings l ON l.id = v.listing_id
     " . $where . " ORDER BY v.verified_at DESC",
    $params,
    $page
);

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require __DIR__ . '/partials/topbar.php';
?>

<div class="admin-content">

    <div class="admin-page-header">
        <div>
            <h2>Verifications</h2>
            <p class="agri-text-muted agri-mb-0"><?= number_format($result['total']) ?> total</p>
        </div>
        <?php if ($listingId): ?>
            <div class="admin-page-actions">
                <a href="verifications.php" class="agri-btn agri-btn-ghost agri-btn-sm">Clear Filter</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="admin-card">
        <div class="admin-card-body admin-card-body-flush">
            <?php if (empty($result['rows'])): ?>
                <div class="agri-empty">
                    <div class="agri-empty-icon">✅</div>
                    <h3>No verifications yet</h3>
                </div>
            <?php else: ?>
                <table class="admin-table" id="verifications-table">
                    <thead>
                        <tr><th>Phone</th><th>Listing</th><th>When</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['rows'] as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars(shortPhone($row['phone']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if ($row['crop']): ?>
                                        <a href="listing-edit.php?id=<?= (int)$row['listing_id'] ?>">
                                            <?= htmlspecialchars($row['crop'] . ' · ' . $row['market'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="agri-text-muted">Deleted listing #<?= (int)$row['listing_id'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="agri-text-sm agri-text-muted"><?= htmlspecialchars(formatDate($row['verified_at']), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?= render_pagination($result['page'], $result['pages'], 'verifications.php?' . http_build_query(['listing_id'=>$listingId])) ?>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
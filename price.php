<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/prices.php');
    exit;
}

$listing = agri_fetch_one("SELECT * FROM agri_listings WHERE id = ?", [$id]);
if (!$listing) {
    header('Location: ' . BASE_URL . '/404.php');
    exit;
}

$pageTitle       = $listing['crop'] . ' in ' . $listing['market'];
$pageDescription = 'Live market price for ' . $listing['crop'] . ' in ' . $listing['market'] . '.';
$currentPage     = 'prices';

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

// Verification history
$verifications = agri_fetch_all(
    "SELECT phone, verified_at FROM agri_verifications
     WHERE listing_id = ?
     ORDER BY verified_at DESC LIMIT 20",
    [$id]
);

// Related listings (same crop, other markets)
$related = agri_fetch_all(
    "SELECT * FROM agri_listings
     WHERE crop = ? AND id != ?
     ORDER BY updated_at DESC LIMIT 3",
    [$listing['crop'], $id]
);

$heroEyebrow  = $listing['crop'] . ' · ' . $listing['market'];
$heroTitle    = formatNaira($listing['price']) . ' per ton';
$heroSubtitle = $listing['is_verified']
    ? 'Verified by ' . (int)$listing['verification_count'] . ' farmers. Updated ' . timeAgo($listing['updated_at']) . '.'
    : 'Pending verification. Updated ' . timeAgo($listing['updated_at']) . '.';
$heroCompact  = true;
$heroActions  = [
    ['label' => '← Back to Prices', 'href' => BASE_URL . '/prices.php', 'variant' => 'secondary'],
];
require __DIR__ . '/partials/hero.php';
?>

<section class="agri-section">
    <div class="agri-container agri-container-narrow">

        <div class="agri-card agri-mb-6">
            <div class="agri-card-header">
                <h3 class="agri-card-title">Price Summary</h3>
                <?= badge_verified($listing['is_verified'], (int)$listing['verification_count']) ?>
            </div>
            <div class="agri-grid agri-grid-2 agri-mt-4">
                <div>
                    <div class="agri-uppercase agri-text-muted">Crop</div>
                    <div class="agri-text-lg agri-font-bold"><?= e($listing['crop']) ?></div>
                </div>
                <div>
                    <div class="agri-uppercase agri-text-muted">Market</div>
                    <div class="agri-text-lg agri-font-bold"><?= e($listing['market']) ?></div>
                </div>
                <div>
                    <div class="agri-uppercase agri-text-muted">Price</div>
                    <div class="agri-text-lg agri-font-bold agri-text-success"><?= e(formatNaira($listing['price'])) ?> / ton</div>
                </div>
                <div>
                    <div class="agri-uppercase agri-text-muted">Last Updated</div>
                    <div class="agri-text-lg agri-font-bold"><?= e(formatDate($listing['updated_at'])) ?></div>
                </div>
            </div>
        </div>

        <!-- NEW: Web Verification Card -->
        <?php if (!$listing['is_verified']): ?>
            <div class="agri-card agri-mb-6" style="background:var(--agri-orange-50);border-color:var(--agri-orange-200);">
                <h3 style="color:var(--agri-orange-900);margin-top:0;">Are you at this market?</h3>
                <p class="agri-text-muted">Help other farmers by confirming this price. Three independent confirmations will mark it as Verified and earn you airtime.</p>
                
                <form id="verify-form">
                    <div class="agri-flex" style="gap:12px;align-items:flex-end;flex-wrap:wrap;">
                        <div class="agri-form-group" style="flex:1;min-width:200px;margin:0;">
                            <label class="agri-label">Your Phone Number</label>
                            <input type="tel" id="verify-phone" class="agri-input" required placeholder="08012345678" data-format-phone>
                        </div>
                        <button type="submit" class="agri-btn agri-btn-primary" id="verify-btn" style="height:42px;">
                            Confirm Price
                        </button>
                    </div>
                    <div id="verify-message" class="agri-mt-4" style="display:none;"></div>
                </form>
            </div>
        <?php endif; ?>

        <div class="agri-card agri-mb-6">
            <h3>Verification History</h3>
            <p class="agri-text-muted">
                Each verification is a confirmation from a farmer physically present at the market.
            </p>

            <?php if (empty($verifications)): ?>
                <p class="agri-text-muted agri-mb-0"><em>No verifications yet.</em></p>
            <?php else: ?>
                <ul style="list-style:none;padding:0;margin:0;">
                    <?php foreach ($verifications as $v): ?>
                        <li style="padding:12px 0;border-bottom:1px solid var(--agri-border);display:flex;justify-content:space-between;align-items:center;">
                            <span><?= e(shortPhone($v['phone'])) ?> confirmed this price</span>
                            <span class="agri-text-xs agri-text-muted"><?= e(timeAgo($v['verified_at'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <?php if (!empty($related)): ?>
            <h3 class="agri-mt-8">Same Crop, Other Markets</h3>
            <div class="agri-grid agri-grid-3">
                <?php foreach ($related as $r): ?>
                    <div class="agri-card agri-card-hover">
                        <div class="agri-uppercase agri-text-muted"><?= e($r['market']) ?></div>
                        <div class="agri-stat-value" style="font-size:1.25rem;"><?= e(formatNaira($r['price'])) ?></div>
                        <p class="agri-text-xs agri-text-muted agri-mb-0"><?= e(timeAgo($r['updated_at'])) ?></p>
                        <a href="?id=<?= (int)$r['id'] ?>" class="agri-btn agri-btn-ghost agri-btn-sm agri-mt-2">View</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- NEW: AJAX Script for Verification -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('verify-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('verify-btn');
        const phone = document.getElementById('verify-phone').value;
        const msg = document.getElementById('verify-message');
        
        btn.disabled = true;
        btn.textContent = 'Verifying...';
        msg.style.display = 'none';

        const formData = new FormData();
        formData.append('listing_id', <?= (int)$listing['id'] ?>);
        formData.append('phone', phone);
        formData.append('source', 'web');

        fetch('<?= BASE_URL ?>/api/verify-price.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            msg.style.display = 'block';
            if (data.success) {
                msg.className = 'agri-alert agri-alert-success';
                msg.innerHTML = '<strong>Success!</strong> ' + data.message;
                // Reload page after 1.5s to show updated count and hide the form
                setTimeout(() => window.location.reload(), 1500);
            } else {
                msg.className = 'agri-alert agri-alert-danger';
                msg.textContent = data.message;
                btn.disabled = false;
                btn.textContent = 'Confirm Price';
            }
        })
        .catch(err => {
            msg.style.display = 'block';
            msg.className = 'agri-alert agri-alert-danger';
            msg.textContent = 'Network error. Please try again.';
            btn.disabled = false;
            btn.textContent = 'Confirm Price';
        });
    });
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
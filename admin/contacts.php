<?php
require_once __DIR__ . '/../includes/functions.php';
agri_boot_admin();

$pageTitle    = 'Contacts';
$pageSubtitle = 'Contact form submissions';
$currentPage  = 'contacts';

$page = max(1, (int)($_GET['page'] ?? 1));

$result = paginate(
    "SELECT COUNT(*) AS c FROM agri_contacts",
    [],
    "SELECT * FROM agri_contacts ORDER BY created_at DESC",
    [],
    $page
);

// Mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    csrf_require();
    agri_query("UPDATE agri_contacts SET is_read = 1 WHERE id = ?", [(int)$_POST['mark_read']]);
    flash_set('success', 'Marked as read.');
    header('Location: contacts.php');
    exit;
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    csrf_require();
    agri_query("DELETE FROM agri_contacts WHERE id = ?", [(int)$_POST['delete_id']]);
    flash_set('success', 'Message deleted.');
    header('Location: contacts.php');
    exit;
}

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require __DIR__ . '/partials/topbar.php';
?>

<div class="admin-content">

    <div class="admin-page-header">
        <div>
            <h2>Contact Messages</h2>
            <p class="agri-text-muted agri-mb-0"><?= number_format($result['total']) ?> total</p>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-body admin-card-body-flush">
            <?php if (empty($result['rows'])): ?>
                <div class="agri-empty">
                    <div class="agri-empty-icon">📩</div>
                    <h3>No messages yet</h3>
                </div>
            <?php else: ?>
                <table class="admin-table" id="contacts-table">
                    <thead>
                        <tr><th>Status</th><th>Name</th><th>Subject</th><th>Received</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['rows'] as $row): ?>
                            <tr>
                                <td>
                                    <?php if (!$row['is_read']): ?>
                                        <span class="agri-badge agri-badge-warning">Unread</span>
                                    <?php else: ?>
                                        <span class="agri-badge agri-badge-gray">Read</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                    <span class="agri-text-xs agri-text-muted"><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['subject'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                    <span class="agri-text-xs agri-text-muted"><?= htmlspecialchars(mb_strimwidth($row['message'], 0, 80, '…'), ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td class="agri-text-sm agri-text-muted"><?= htmlspecialchars(timeAgo($row['created_at']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="admin-table-actions">
                                    <?php if (!$row['is_read']): ?>
                                        <form method="post" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="mark_read" value="<?= (int)$row['id'] ?>">
                                            <button type="submit" class="admin-action-view" style="cursor:pointer;border:none;">Mark Read</button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this message?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="delete_id" value="<?= (int)$row['id'] ?>">
                                        <button type="submit" class="admin-action-delete" style="cursor:pointer;border:none;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5" style="background:var(--agri-gray-100);padding:12px 16px;font-size:13px;">
                                    <strong>Message:</strong> <?= nl2br(htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8')) ?>
                                    <?php if ($row['phone']): ?>
                                        <br><strong>Phone:</strong> <?= htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8') ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?= render_pagination($result['page'], $result['pages'], 'contacts.php') ?>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
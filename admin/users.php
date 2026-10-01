<?php
require_once __DIR__ . '/../includes/functions.php';
agri_boot_admin();
auth_require_admin();

$pageTitle    = 'Admin Users';
$pageSubtitle = 'Manage who can access the dashboard';
$currentPage  = 'users';

$errors = [];

// Add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    csrf_require();
    $result = auth_create_admin(
        trim($_POST['username'] ?? ''),
        $_POST['password'] ?? '',
        trim($_POST['full_name'] ?? ''),
        trim($_POST['email'] ?? ''),
        $_POST['role'] ?? 'admin'
    );
    if ($result['success']) {
        flash_set('success', $result['message']);
        header('Location: users.php');
        exit;
    }
    $errors['general'] = $result['message'];
}

// Toggle active
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    csrf_require();
    $tid = (int)$_POST['toggle_id'];
    if ($tid !== auth_id()) {
        agri_query("UPDATE agri_admins SET is_active = NOT is_active WHERE id = ?", [$tid]);
        flash_set('success', 'User status updated.');
    } else {
        flash_set('danger', 'You cannot deactivate your own account.');
    }
    header('Location: users.php');
    exit;
}

$users = agri_fetch_all("SELECT * FROM agri_admins ORDER BY created_at DESC");

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require __DIR__ . '/partials/topbar.php';
?>

<div class="admin-content">

    <div class="admin-page-header">
        <div>
            <h2>Admin Users</h2>
            <p class="agri-text-muted agri-mb-0"><?= count($users) ?> accounts</p>
        </div>
        <div class="admin-page-actions">
            <button class="agri-btn agri-btn-primary agri-btn-sm" data-modal-open="add-user">+ Add User</button>
        </div>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="agri-alert agri-alert-danger"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <div class="admin-card">
        <div class="admin-card-body admin-card-body-flush">
            <table class="admin-table">
                <thead>
                    <tr><th>Username</th><th>Name</th><th>Role</th><th>Last Login</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($u['full_name'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="agri-badge agri-badge-<?= $u['role'] === 'admin' ? 'success' : 'gray' ?>"><?= htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td class="agri-text-sm agri-text-muted"><?= $u['last_login'] ? htmlspecialchars(timeAgo($u['last_login']), ENT_QUOTES, 'UTF-8') : 'Never' ?></td>
                            <td>
                                <?php if ($u['is_active']): ?>
                                    <span class="agri-badge agri-badge-success">Active</span>
                                <?php else: ?>
                                    <span class="agri-badge agri-badge-danger">Disabled</span>
                                <?php endif; ?>
                            </td>
                            <td class="admin-table-actions">
                                <?php if ($u['id'] !== auth_id()): ?>
                                    <form method="post" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="toggle_id" value="<?= (int)$u['id'] ?>">
                                        <button type="submit" class="admin-action-edit" style="cursor:pointer;border:none;">
                                            <?= $u['is_active'] ? 'Disable' : 'Enable' ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="agri-text-xs agri-text-muted">(you)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="admin-modal-backdrop" id="add-user">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Add Admin User</h3>
            <button class="admin-modal-close" data-modal-close>&times;</button>
        </div>
        <form method="post" data-validate-form>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add">

            <div class="admin-modal-body">
                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Username</label>
                    <input type="text" name="username" class="agri-input" required maxlength="50">
                </div>
                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Password (min 8 chars)</label>
                    <input type="password" name="password" class="agri-input" required minlength="8">
                </div>
                <div class="agri-form-group">
                    <label class="agri-label">Full Name</label>
                    <input type="text" name="full_name" class="agri-input" maxlength="100">
                </div>
                <div class="agri-form-group">
                    <label class="agri-label">Email</label>
                    <input type="email" name="email" class="agri-input" maxlength="100">
                </div>
                <div class="agri-form-group">
                    <label class="agri-label agri-label-required">Role</label>
                    <select name="role" class="agri-select" required>
                        <option value="admin">Admin (full access)</option>
                        <option value="editor">Editor (content only)</option>
                    </select>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="agri-btn agri-btn-ghost" data-modal-close>Cancel</button>
                <button type="submit" class="agri-btn agri-btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/validation.php';

$pageTitle       = 'Contact';
$pageDescription = 'Get in touch with the AgriConnect team.';
$currentPage     = 'contact';

// Ensure contact table exists
agri_query("
    CREATE TABLE IF NOT EXISTS agri_contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(120) NULL,
        phone VARCHAR(20) NULL,
        subject VARCHAR(150) NOT NULL,
        message TEXT NOT NULL,
        ip_address VARCHAR(45) NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_created (created_at),
        INDEX idx_read (is_read)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$success = '';
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $result = validate_form($_POST, [
        'name'    => ['label' => 'Name',    'rules' => 'required|min:2|max:100'],
        'email'   => ['label' => 'Email',   'rules' => 'required|email', 'sanitize' => 'email'],
        'phone'   => ['label' => 'Phone',   'rules' => 'phone', 'sanitize' => 'phone'],
        'subject' => ['label' => 'Subject', 'rules' => 'required|min:3|max:150'],
        'message' => ['label' => 'Message', 'rules' => 'required|min:10|max:2000'],
    ]);

    if ($result['valid']) {
        $d = $result['data'];
        agri_query(
            "INSERT INTO agri_contacts (name, email, phone, subject, message, ip_address)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$d['name'], $d['email'], $d['phone'], $d['subject'], $d['message'], $_SERVER['REMOTE_ADDR'] ?? '']
        );

        // Email ACK + admin notification
        if (file_exists(__DIR__ . '/includes/mail.php')) {
            require_once __DIR__ . '/includes/mail.php';
            @mail_contact_ack($d['email'], $d['name']);
            @mail_admin_notification('New Contact: ' . $d['subject'], "From: {$d['name']} <{$d['email']}>\n\n{$d['message']}");
        }

        $success = 'Thanks! Your message has been sent. We will reply within 24 hours.';
    } else {
        $errors = $result['errors'];
    }
}

require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$heroEyebrow  = 'Get in Touch';
$heroTitle    = 'Contact Us';
$heroSubtitle = 'Questions, feedback, or partnership ideas — we want to hear from you.';
$heroCompact  = true;
$heroActions  = [];
require __DIR__ . '/partials/hero.php';
?>

<section class="agri-section">
    <div class="agri-container">
        <div class="agri-grid agri-grid-2" style="gap:40px;align-items:flex-start;">

            <div>
                <h2>Reach the Team</h2>
                <p class="agri-text-muted">We usually respond within 24 hours.</p>

                <div class="agri-card agri-mb-4">
                    <div class="agri-uppercase agri-text-muted agri-text-xs">Email</div>
                    <div class="agri-font-bold"><?= e(MAIL_ADMIN_EMAIL) ?></div>
                </div>

                <div class="agri-card agri-mb-4">
                    <div class="agri-uppercase agri-text-muted agri-text-xs">USSD</div>
                    <div class="agri-font-bold"><?= e(AT_USSD_CODE) ?></div>
                </div>

                <div class="agri-card">
                    <div class="agri-uppercase agri-text-muted agri-text-xs">Location</div>
                    <div class="agri-font-bold">Kano, Nigeria</div>
                </div>
            </div>

            <div class="agri-card">
                <?php if ($success): ?>
                    <div class="agri-alert agri-alert-success" data-auto-dismiss="6000"><?= e($success) ?></div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="agri-alert agri-alert-danger">Please fix the errors below.</div>
                <?php endif; ?>

                <form method="post" data-validate-form>
                    <?= csrf_field() ?>

                    <div class="agri-form-group">
                        <label class="agri-label agri-label-required">Full Name</label>
                        <input type="text" name="name" class="agri-input" required maxlength="100">
                        <?php if (!empty($errors['name'])): ?><div class="agri-form-error"><?= e($errors['name']) ?></div><?php endif; ?>
                    </div>

                    <div class="agri-form-group">
                        <label class="agri-label agri-label-required">Email</label>
                        <input type="email" name="email" class="agri-input" required>
                        <?php if (!empty($errors['email'])): ?><div class="agri-form-error"><?= e($errors['email']) ?></div><?php endif; ?>
                    </div>

                    <div class="agri-form-group">
                        <label class="agri-label">Phone (optional)</label>
                        <input type="tel" name="phone" class="agri-input" data-format-phone placeholder="08012345678">
                        <?php if (!empty($errors['phone'])): ?><div class="agri-form-error"><?= e($errors['phone']) ?></div><?php endif; ?>
                    </div>

                    <div class="agri-form-group">
                        <label class="agri-label agri-label-required">Subject</label>
                        <input type="text" name="subject" class="agri-input" required maxlength="150">
                        <?php if (!empty($errors['subject'])): ?><div class="agri-form-error"><?= e($errors['subject']) ?></div><?php endif; ?>
                    </div>

                    <div class="agri-form-group">
                        <label class="agri-label agri-label-required">Message</label>
                        <textarea name="message" class="agri-textarea" required maxlength="2000"></textarea>
                        <?php if (!empty($errors['message'])): ?><div class="agri-form-error"><?= e($errors['message']) ?></div><?php endif; ?>
                    </div>

                    <button type="submit" class="agri-btn agri-btn-primary agri-btn-lg agri-btn-block">
                        Send Message
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php';
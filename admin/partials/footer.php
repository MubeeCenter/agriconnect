<?php
/**
 * Admin Layout — Footer
 * Closes <main>, .admin-layout, loads JS, closes </body></html>
 */

if (!defined('AGRICONNECT')) {
    require_once __DIR__ . '/../../includes/config.php';
}
?>
    </main><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<!-- Scripts — order matters -->
<script src="<?= BASE_URL ?>/assets/js/main.js?v=<?= APP_VERSION ?>"></script>
<script src="<?= BASE_URL ?>/assets/js/admin.js?v=<?= APP_VERSION ?>"></script>
<script src="<?= BASE_URL ?>/assets/js/validation.js?v=<?= APP_VERSION ?>"></script>

<?php
// Optional per-page scripts
if (!empty($pageScripts) && is_array($pageScripts)) {
    foreach ($pageScripts as $script) {
        echo '<script src="' . htmlspecialchars($script, ENT_QUOTES, 'UTF-8') . '"></script>' . "\n";
    }
}
?>

</body>
</html>
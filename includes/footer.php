<?php
?>
</main>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <h4><i class="bi bi-shield-check"></i> Ethiopia Vital Events</h4>
                <p class="small"><?php echo getTranslation('hero_subtitle'); ?></p>
            </div>
            <div>
                <h4><?php echo getTranslation('quick_links'); ?></h4>
                <ul class="footer-links">
                    <li><a href="index.php"><?php echo getTranslation('home'); ?></a></li>
                    <li><a href="about.php"><?php echo getTranslation('about'); ?></a></li>
                    <li><a href="contact.php"><?php echo getTranslation('contact'); ?></a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php"><?php echo getTranslation('dashboard'); ?></a></li>
                        <li><a href="profile.php"><?php echo getTranslation('profile'); ?></a></li>
                    <?php else: ?>
                        <li><a href="login_simple.php"><?php echo getTranslation('login'); ?></a></li>
                        <li><a href="register_simple.php"><?php echo getTranslation('register'); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div>
                <h4><?php echo getTranslation('services'); ?></h4>
                <ul class="footer-links">
                    <li><a href="../login_simple.php"><?php echo getTranslation('birth_registration'); ?></a></li>
                    <li><a href="../login_simple.php"><?php echo getTranslation('marriage_registration'); ?></a></li>
                    <li><a href="../login_simple.php"><?php echo getTranslation('divorce_registration'); ?></a></li>
                    <li><a href="../login_simple.php"><?php echo getTranslation('death_registration'); ?></a></li>
                </ul>
            </div>
            <div>
                <h4><?php echo getTranslation('contact_info'); ?></h4>
                <ul class="footer-links">
                    <li><i class="bi bi-geo-alt me-2"></i> Addis Ababa, Ethiopia</li>
                    <li><i class="bi bi-envelope me-2"></i> info@vitalevents.gov.et</li>
                    <li><i class="bi bi-telephone me-2"></i> +251-954-197-7521</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Ethiopia Vital Events Registration Agency. <?php echo getTranslation('all_rights_reserved'); ?></p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
require_once 'functions.php';

// Handle language change from URL
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (in_array($lang, ['en', 'am'])) {
        setLanguage($lang);
    }
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

$current_lang = getCurrentLanguage();
$is_profile_page = basename($_SERVER['PHP_SELF']) == 'profile.php';
$is_dashboard_page = basename($_SERVER['PHP_SELF']) == 'dashboard.php';
?>


<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?php echo getTranslation('hero_title'); ?> - Ethiopia Civil Registration</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo d-flex align-items-center gap-2 text-decoration-none">
                <!-- Logo Image - Change this to your local image -->
                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center overflow-hidden shadow-sm" style="width: 50px; height: 50px;">
                    <img src="assets/images/logo.png" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://placehold.co/45x45/005dac/white?text=VE'">
                </div>
                <span class="fw-bold fs-2 text-primary ">Vital Events</span>
            </a>

            <button class="mobile-menu-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenuCanvas">
                <i class="bi bi-list"></i>
            </button>

            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($is_profile_page): ?>
                        <a href="dashboard.php"><?php echo getTranslation('dashboard'); ?></a>
                        <a href="profile.php"><?php echo getTranslation('profile'); ?></a>
                        <a href="logout.php"><?php echo getTranslation('logout'); ?></a>
                    <?php else: ?>
                        <a href="dashboard.php"><?php echo getTranslation('dashboard'); ?></a>
                        <a href="process/submit_birth.php" id="navBirth" data-service="birth"><?php echo getTranslation('birth_registration'); ?></a>
                        <a href="#" id="navMarriage" data-service="marriage"><?php echo getTranslation('marriage_registration'); ?></a>
                        <a href="#" id="navDivorce" data-service="divorce"><?php echo getTranslation('divorce_registration'); ?></a>
                        <a href="#" id="navDeath" data-service="death"><?php echo getTranslation('death_registration'); ?></a>
                        <a href="profile.php"><?php echo getTranslation('profile'); ?></a>
                        <a href="logout.php"><?php echo getTranslation('logout'); ?></a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="index.php"><?php echo getTranslation('home'); ?></a>
                    <a href="about.php"><?php echo getTranslation('about'); ?></a>
                    <a href="contact.php"><?php echo getTranslation('contact'); ?></a>
                    <a href="login_simple.php"><?php echo getTranslation('login'); ?></a>
                    <a href="register_simple.php"><?php echo getTranslation('register'); ?></a>
                <?php endif; ?>

                <div class="lang-selector">
                    <a href="?lang=en" class="lang-btn <?php echo $current_lang == 'en' ? 'active' : ''; ?>">EN</a>
                    <a href="?lang=am" class="lang-btn <?php echo $current_lang == 'am' ? 'active' : ''; ?>">አማ</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenuCanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title text-primary">Vital Events</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($is_profile_page): ?>
                    <a href="dashboard.php" class="nav-link"><?php echo getTranslation('dashboard'); ?></a>
                    <a href="profile.php" class="nav-link"><?php echo getTranslation('profile'); ?></a>
                    <a href="logout.php" class="nav-link text-danger"><?php echo getTranslation('logout'); ?></a>
                <?php else: ?>
                    <a href="dashboard.php" class="nav-link"><?php echo getTranslation('dashboard'); ?></a>
                    <a href="#" class="nav-link" data-service="birth"><?php echo getTranslation('birth_registration'); ?></a>
                    <a href="#" class="nav-link" data-service="marriage"><?php echo getTranslation('marriage_registration'); ?></a>
                    <a href="#" class="nav-link" data-service="divorce"><?php echo getTranslation('divorce_registration'); ?></a>
                    <a href="#" class="nav-link" data-service="death"><?php echo getTranslation('death_registration'); ?></a>
                    <a href="profile.php" class="nav-link"><?php echo getTranslation('profile'); ?></a>
                    <a href="logout.php" class="nav-link text-danger"><?php echo getTranslation('logout'); ?></a>
                <?php endif; ?>
            <?php else: ?>
                <a href="index.php" class="nav-link"><?php echo getTranslation('home'); ?></a>
                <a href="about.php" class="nav-link"><?php echo getTranslation('about'); ?></a>
                <a href="contact.php" class="nav-link"><?php echo getTranslation('contact'); ?></a>
                <a href="login_simple.php" class="nav-link"><?php echo getTranslation('login'); ?></a>
                <a href="register_simple.php" class="nav-link"><?php echo getTranslation('register'); ?></a>
            <?php endif; ?>
            <hr>
            <div class="lang-selector d-flex justify-content-center">
                <a href="?lang=en" class="lang-btn <?php echo $current_lang == 'en' ? 'active' : ''; ?>">EN</a>
                <a href="?lang=am" class="lang-btn <?php echo $current_lang == 'am' ? 'active' : ''; ?>">አማ</a>
            </div>
        </div>
    </div>

    <main class="<?php echo $is_profile_page ? 'mt-5 pt-4' : ''; ?>">
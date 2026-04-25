<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$current_lang = getCurrentLanguage();

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");

    if (mysqli_num_rows($check) > 0) {
        $error = getTranslation('email_already_registered');
    } elseif ($password !== $confirm) {
        $error = getTranslation('passwords_do_not_match');
    } elseif (strlen($password) < 6) {
        $error = getTranslation('password_min_length');
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (full_name, email, phone, password) VALUES ('$full_name', '$email', '$phone', '$hashed')";

        if (mysqli_query($conn, $sql)) {
            $success = getTranslation('registration_success_message');
            $_POST = array();
        } else {
            $error = getTranslation('registration_failed_message');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getTranslation('register'); ?> - Vital Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body style="   background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-body p-4 p-md-5">
                    <div class="logoo">
                        <i class="bi bi-person-plus-fill"></i>
                        <h3 class="fw-bold mt-2">Vital Events</h3>
                        <p class="text-muted"><?php echo getTranslation('register'); ?></p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?> <a href="login_simple.php"><?php echo getTranslation('login_here'); ?></a></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo getTranslation('full_name'); ?></label>
                            <input type="text" name="full_name" class="form-control form-control-lg" value="<?php echo $_POST['full_name'] ?? ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo getTranslation('email'); ?></label>
                            <input type="email" name="email" class="form-control form-control-lg" value="<?php echo $_POST['email'] ?? ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo getTranslation('phone'); ?></label>
                            <input type="text" name="phone" class="form-control form-control-lg" value="<?php echo $_POST['phone'] ?? ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo getTranslation('password'); ?></label>
                            <input type="password" name="password" class="form-control form-control-lg" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo getTranslation('confirm_password'); ?></label>
                            <input type="password" name="confirm_password" class="form-control form-control-lg" required>
                        </div>
                        <button type="submit" class="btn btn-login btn-primary-custom w-100 btn-lg"><?php echo getTranslation('register'); ?></button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-2"><?php echo getTranslation('already_have_account'); ?> <a href="login_simple.php"><?php echo getTranslation('login_here'); ?></a></p>
                        <a href="index.php" class="btn btn-back w-100 mt-2"><i class="bi bi-arrow-left"></i> <?php echo getTranslation('back_to_home'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
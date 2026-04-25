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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = getTranslation('invalid_password');
        }
    } else {
        $error = getTranslation('email_not_found');
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getTranslation('login'); ?> - Vital Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>


<body style=" background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-body p-4 p-md-5">
                    <div class="logoo">
                        <i class="bi bi-shield-check"></i>
                        <h3 class="fw-bold mt-2">Vital Events</h3>
                        <p class="text-muted"><?php echo getTranslation('login'); ?></p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo getTranslation('email'); ?></label>
                            <input type="email" name="email" class="form-control form-control-lg" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo getTranslation('password'); ?></label>
                            <input type="password" name="password" class="form-control form-control-lg" placeholder="Enter your password" required>
                        </div>
                        <button type="submit" class="btn btn-login btn-primary-custom w-100 btn-lg"><?php echo getTranslation('login'); ?></button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-2"><?php echo getTranslation('dont_have_account'); ?> <a href="register_simple.php"><?php echo getTranslation('register_here'); ?></a></p>
                        <a href="index.php" class="btn btn-back w-100 mt-2"><i class="bi bi-arrow-left"></i> <?php echo getTranslation('back_to_home'); ?></a>
                    </div>

                    <hr class="my-4">
                    <div class="text-center">
                        <small class="text-muted">
                            <strong><?php echo getTranslation('demo_accounts'); ?>:</strong><br>
                            <?php echo getTranslation('admin_account'); ?><br>
                            <?php echo getTranslation('user_account'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
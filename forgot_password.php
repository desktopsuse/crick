<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';
/** @var mysqli $conn */
global $conn;

// Ensure the password_resets table exists
$conn->query("CREATE TABLE IF NOT EXISTS password_resets (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$current_lang = getCurrentLanguage();
$error = '';
$success = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);

    if (empty($email)) {
        $error = 'Please enter your email';
    } else {
        // Check if the email exists
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            // Remove any existing unused tokens for this email
            $conn->query("DELETE FROM password_resets WHERE email = '$email' AND used = 0");

            // Generate a reset token valid for 1 hour
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $insert = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert);
            mysqli_stmt_bind_param($stmt, "sss", $email, $token, $expires_at);
            mysqli_stmt_execute($stmt);

            $reset_link = SITE_URL . 'reset_password.php?token=' . $token;
            $success = 'A password reset link has been generated for ' . htmlspecialchars($email) . '.<br><a href="' . $reset_link . '" class="btn btn-primary-custom btn-sm mt-2">Reset Password</a>';
            $email = '';
        } else {
            $error = getTranslation('email_not_found');
        }
    }
}

?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getTranslation('forgot_password'); ?> - Vital Events</title>
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
                        <p class="text-muted"><?php echo getTranslation('forgot_password'); ?></p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo getTranslation('email'); ?></label>
                            <input type="email" name="email" class="form-control form-control-lg" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-login btn-primary-custom w-100 btn-lg"><?php echo getTranslation('forgot_password'); ?></button>
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

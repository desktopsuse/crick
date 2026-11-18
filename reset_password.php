<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';
/** @var mysqli $conn */
global $conn;

$current_lang = getCurrentLanguage();
$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: forgot_password.php');
    exit();
}

// Validate the token: must exist, be unused, and not expired
$sql = "SELECT id, email FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {
    $error = 'Invalid or expired reset link. Please request a new one.';
    $token = '';
} else {
    $reset = mysqli_fetch_assoc($result);
    $reset_email = $reset['email'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($password) || empty($confirm_password)) {
            $error = 'Please enter and confirm your new password';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Update the user's password
            $update = "UPDATE users SET password = ? WHERE email = ?";
            $stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $reset_email);
            mysqli_stmt_execute($stmt);

            // Mark the token as used
            $conn->query("UPDATE password_resets SET used = 1 WHERE token = '$token'");

            $success = 'Your password has been reset successfully. You can now <a href="login_simple.php">log in</a>.';
            $token = '';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Reset Complete' : 'Reset Password'; ?> - Vital Events</title>
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
                        <i class="bi bi-key"></i>
                        <h3 class="fw-bold mt-2">Vital Events</h3>
                        <p class="text-muted"><?php echo $success ? 'Password Reset' : 'Reset Password'; ?></p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">New Password</label>
                                <input type="password" name="password" class="form-control form-control-lg" placeholder="Enter new password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control form-control-lg" placeholder="Confirm new password" required>
                            </div>
                            <button type="submit" class="btn btn-login btn-primary-custom w-100 btn-lg">Reset Password</button>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <a href="login_simple.php" class="btn btn-back w-100 mt-2"><i class="bi bi-arrow-left"></i> Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

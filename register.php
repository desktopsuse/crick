<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
/** @var mysqli $conn */
global $conn;

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $national_id = sanitize($_POST['national_id']);
    $address = sanitize($_POST['address']);
    
    if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
        $error = 'Please fill all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Email already registered';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (full_name, email, phone, password, national_id, address) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssss", $full_name, $email, $phone, $hashed_password, $national_id, $address);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Registration successful! You can now login.';
                $_POST = array();
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h2 class="text-center fw-bold mb-4">Create Account</h2>
                    
                    <?php if ($error): ?>
                        <div class="message error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="message success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name *</label>
                            <input type="text" name="full_name" class="form-control form-control-custom" value="<?php echo $_POST['full_name'] ?? ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email *</label>
                            <input type="email" name="email" class="form-control form-control-custom" value="<?php echo $_POST['email'] ?? ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone Number *</label>
                            <input type="tel" name="phone" class="form-control form-control-custom" value="<?php echo $_POST['phone'] ?? ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">National ID</label>
                            <input type="text" name="national_id" class="form-control form-control-custom" value="<?php echo $_POST['national_id'] ?? ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" class="form-control form-control-custom" rows="2"><?php echo $_POST['address'] ?? ''; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password *</label>
                            <input type="password" name="password" class="form-control form-control-custom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-control form-control-custom" required>
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100">Register</button>
                    </form>
                    
                    <p class="text-center mt-3">
                        Already have an account? <a href="login.php">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
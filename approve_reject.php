<?php
require_once 'includes/config.php';
        /** @var mysqli $conn */
        global $conn;
session_start();

$admin_password = "admin123";

if (!isset($_SESSION['admin_logged_in'])) {
    if (isset($_POST['password'])) {
        if ($_POST['password'] == $admin_password) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name'] = 'Administrator';
            $_SESSION['admin_id'] = 1;
        } else {
            $error = "Wrong password!";
        }
    }

    if (!isset($_SESSION['admin_logged_in'])) {
?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>Admin Login - Vital Events</title>
            <link rel="stylesheet" href="assets/css/style.css">
            <style>
                /* Additional inline styles for login page only - minimal and non-duplicative */
                body.login-page {
                    font-family: 'Inter', sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }
            </style>
        </head>

        <body class="login-page">
            <div class="login-box">
                <h2>Admin Login</h2>
                <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
                <form method="POST">
                    <input type="password" name="password" placeholder="Enter Admin Password" required>
                    <button type="submit">Login</button>
                </form>
               
            </div>
        </body>

        </html>
<?php
        exit();
    }
}

$admin_name = $_SESSION['admin_name'] ?? 'Administrator';
$admin_id = $_SESSION['admin_id'] ?? 1;

// Get all pending registrations
$pending = array();

$result = mysqli_query($conn, "SELECT id, child_name as name, 'birth' as type, registration_number, submitted_at FROM birth_registrations WHERE status='pending'");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) $pending[] = $row;
}

$result = mysqli_query($conn, "SELECT id, CONCAT(groom_name,' & ',bride_name) as name, 'marriage' as type, registration_number, submitted_at FROM marriage_registrations WHERE status='pending'");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) $pending[] = $row;
}

$result = mysqli_query($conn, "SELECT id, CONCAT(husband_name,' & ',wife_name) as name, 'divorce' as type, registration_number, submitted_at FROM divorce_registrations WHERE status='pending'");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) $pending[] = $row;
}

$result = mysqli_query($conn, "SELECT id, deceased_name as name, 'death' as type, registration_number, submitted_at FROM death_registrations WHERE status='pending'");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) $pending[] = $row;
}

// Handle Approve - SIMPLE VERSION (no signature)
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $type = $_GET['type'];
    $cert_number = 'CERT-' . strtoupper($type) . '-' . date('Y') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);

    $sql = "UPDATE {$type}_registrations SET status='approved', certificate_number='$cert_number', approved_at=NOW(), approved_by='$admin_id', registrar_full_name='$admin_name' WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('✅ Approved by: $admin_name!\\nCertificate: $cert_number'); window.location='approve_reject.php';</script>";
    } else {
        echo "<script>alert('❌ Error: " . mysqli_error($conn) . "'); window.location='approve_reject.php';</script>";
    }
}

// Handle Reject
if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $type = $_GET['type'];

    $sql = "UPDATE {$type}_registrations SET status='rejected' WHERE id=$id";
    mysqli_query($conn, $sql);
    echo "<script>alert('❌ Rejected!'); window.location='approve_reject.php';</script>";
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: approve_reject.php');
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Panel - Approve/Reject</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="admin-panel-page">
    <div class="admin-container">
        <div class="admin-header">
            <h1>Admin Panel - Approve / Reject Registrations</h1>
            <a href="?logout=1" class="logout-btn" onclick="return confirm('Logout?')">Logout</a>
        </div>

        <div class="admin-stats">
            Total Pending: <span><?php echo count($pending); ?></span>
        </div>

        <?php if (count($pending) > 0): ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Reg Number</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending as $item): ?>
                            <tr>
                                <td>#<?php echo $item['id']; ?></td>
                                <td><strong><?php echo ucfirst($item['type']); ?></strong></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo $item['registration_number']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($item['submitted_at'])); ?></td>
                                <td>
                                    <a href="?approve=<?php echo $item['id']; ?>&type=<?php echo $item['type']; ?>" class="btn-approve" onclick="return confirm('Approve this registration?')">✅ Approve</a>
                                    <a href="?reject=<?php echo $item['id']; ?>&type=<?php echo $item['type']; ?>" class="btn-reject" onclick="return confirm('Reject this registration?')">❌ Reject</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="admin-empty">No pending registrations! All done.</div>
        <?php endif; ?>

        <div class="admin-footer">
            <a href="dashboard.php">Dashboard</a> |
            <a href="index.php">Home</a>
        </div>
    </div>
</body>

</html>
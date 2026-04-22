<?php
require_once 'includes/config.php';
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
            <style>
                body {
                    font-family: Arial;
                    background: #f0f2f5;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }

                .login-box {
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
                    width: 350px;
                    text-align: center;
                }

                .login-box h2 {
                    color: #005dac;
                    margin-bottom: 20px;
                }

                .login-box input {
                    width: 100%;
                    padding: 12px;
                    margin: 10px 0;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }

                .login-box button {
                    background: #005dac;
                    color: white;
                    padding: 12px;
                    border: none;
                    border-radius: 5px;
                    width: 100%;
                    cursor: pointer;
                }

                .login-box button:hover {
                    background: #004786;
                }

                .error {
                    color: red;
                    margin-bottom: 10px;
                }
            </style>
        </head>

        <body>
            <div class="login-box">
                <h2>Admin Login</h2>
                <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
                <form method="POST">
                    <input type="password" name="password" placeholder="Enter Admin Password" required>
                    <button type="submit">Login</button>
                </form>
                <p style="margin-top:15px; font-size:12px; color:#666;">Default password: admin123</p>
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: #005dac;
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 20px;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: #c82333;
        }

        .stats {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .stats span {
            font-size: 28px;
            font-weight: bold;
            color: #005dac;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #005dac;
            color: white;
        }

        .btn-approve {
            background: #28a745;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 5px;
        }

        .btn-reject {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-approve:hover {
            background: #1e7e34;
        }

        .btn-reject:hover {
            background: #c82333;
        }

        .empty {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            color: #666;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
        }

        .footer a {
            color: #005dac;
            text-decoration: none;
            margin: 0 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Admin Panel - Approve / Reject Registrations</h1>
            <a href="?logout=1" class="logout-btn" onclick="return confirm('Logout?')">Logout</a>
        </div>

        <div class="stats">
            Total Pending: <span><?php echo count($pending); ?></span>
        </div>

        <?php if (count($pending) > 0): ?>
            <table>
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
        <?php else: ?>
            <div class="empty">No pending registrations! All done.</div>
        <?php endif; ?>

        <div class="footer">
            <a href="dashboard.php">Dashboard</a> |
            <a href="index.php">Home</a>
        </div>
    </div>
</body>

</html>
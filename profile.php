<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

/** @var mysqli $conn */
global $conn;

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login_simple.php');
    exit();
}

$user = getUserById($_SESSION['user_id']);
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $national_id = sanitize($_POST['national_id']);
    
    $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "si", $email, $_SESSION['user_id']);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = getTranslation('email_exists');
    } else {
        $sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ?, national_id = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssi", $full_name, $email, $phone, $address, $national_id, $_SESSION['user_id']);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = getTranslation('profile_updated');
            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_email'] = $email;
            $user = getUserById($_SESSION['user_id']);
        } else {
            $error = getTranslation('registration_failed');
        }
    }
}

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_photo']['name'];
        $fileext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($fileext, $allowed)) {
            $new_filename = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $fileext;
            $upload_path = 'assets/uploads/' . $new_filename;
            
            if (!file_exists('assets/uploads')) {
                mkdir('assets/uploads', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_path)) {
                if ($user['profile_image'] != 'profile-placeholder.jpg' && file_exists('assets/uploads/' . $user['profile_image'])) {
                    unlink('assets/uploads/' . $user['profile_image']);
                }
                
                $sql = "UPDATE users SET profile_image = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $new_filename, $_SESSION['user_id']);
                mysqli_stmt_execute($stmt);
                
                $success = getTranslation('profile_updated');
                $user = getUserById($_SESSION['user_id']);
            } else {
                $error = 'Failed to upload image';
            }
        } else {
            $error = 'Only JPG, PNG, GIF images are allowed';
        }
    }
}

// Get all certificates
$certificates = [];

$birth_sql = "SELECT id, child_name as title, registration_number, certificate_number, approved_at, status, 'birth' as type FROM birth_registrations WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $birth_sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) $certificates[] = $row;

$marriage_sql = "SELECT id, CONCAT(groom_name, ' & ', bride_name) as title, registration_number, certificate_number, approved_at, status, 'marriage' as type FROM marriage_registrations WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $marriage_sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) $certificates[] = $row;

$divorce_sql = "SELECT id, CONCAT(husband_name, ' & ', wife_name) as title, registration_number, certificate_number, approved_at, status, 'divorce' as type FROM divorce_registrations WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $divorce_sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) $certificates[] = $row;

$death_sql = "SELECT id, deceased_name as title, registration_number, certificate_number, approved_at, status, 'death' as type FROM death_registrations WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $death_sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) $certificates[] = $row;

include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Back to Dashboard Button -->
    <div class="mb-3">
        <a href="dashboard.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> <?php echo getTranslation('back_to_dashboard'); ?>
        </a>
    </div>

    <!-- Profile Header -->
    <div class="bg-white rounded-4 p-4 p-md-5 shadow-sm mb-4">
        <div class="text-center">
            <div class="position-relative d-inline-block">
                <?php 
                $profile_image = !empty($user['profile_image']) && $user['profile_image'] != 'profile-placeholder.jpg' && file_exists('assets/uploads/' . $user['profile_image'])
                    ? 'assets/uploads/' . $user['profile_image'] 
                    : 'assets/images/profile-placeholder.jpg';
                ?>
                <img src="<?php echo $profile_image; ?>" alt="Profile Photo" class="profile-avatar" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 4px solid #005dac;">
                
                <button class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0" style="width: 40px; height: 40px;" data-bs-toggle="modal" data-bs-target="#photoModal">
                    <i class="bi bi-camera-fill"></i>
                </button>
            </div>
            
            <h2 class="fw-bold mt-3"><?php echo htmlspecialchars($user['full_name']); ?></h2>
            <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
            <p class="text-muted small"><?php echo getTranslation('national_id'); ?>: <?php echo htmlspecialchars($user['national_id'] ?? getTranslation('not_provided')); ?></p>
        </div>
        <hr>
        
        <h5 class="fw-bold mb-3"><?php echo getTranslation('edit_profile'); ?></h5>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="update_profile" value="1">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold"><?php echo getTranslation('full_name'); ?></label>
                    <input type="text" name="full_name" class="form-control form-control-custom" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold"><?php echo getTranslation('email'); ?></label>
                    <input type="email" name="email" class="form-control form-control-custom" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold"><?php echo getTranslation('phone'); ?></label>
                    <input type="tel" name="phone" class="form-control form-control-custom" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold"><?php echo getTranslation('national_id'); ?></label>
                    <input type="text" name="national_id" class="form-control form-control-custom" value="<?php echo htmlspecialchars($user['national_id'] ?? ''); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold"><?php echo getTranslation('address'); ?></label>
                    <input type="text" name="address" class="form-control form-control-custom"><?php echo htmlspecialchars($user['address'] ?? ''); ?></input>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary-custom"><?php echo getTranslation('save_changes'); ?></button>
                </div>
            </div>
        </form>
    </div>

    <!-- Certificates Section -->
    <h3 class="fw-bold my-5 text-center fs-1"><?php echo getTranslation('Certificates'); ?></h3>
    
    <?php if (count($certificates) > 0): ?>
        <div class="row g-3">
            <?php foreach ($certificates as $cert): 
                $type_class = $cert['type'] == 'birth' ? 'primary' : ($cert['type'] == 'marriage' ? 'success' : ($cert['type'] == 'divorce' ? 'warning' : 'danger'));
                $type_icon = $cert['type'] == 'birth' ? 'bi-gift-fill' : ($cert['type'] == 'marriage' ? 'bi-suit-heart-fill' : ($cert['type'] == 'divorce' ? 'bi-heartbreak-fill' : 'bi-tree-fill'));
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="certificate-card border rounded-4 p-3 bg-white shadow-sm h-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi <?php echo $type_icon; ?> fs-2 text-<?php echo $type_class; ?>"></i>
                                <div>
                                    <h6 class="fw-bold mb-0"><?php echo ucfirst($cert['type']); ?> <?php echo getTranslation('certificate'); ?></h6>
                                    <small class="text-muted"><?php echo getTranslation('certificate_number'); ?>: <?php echo $cert['certificate_number'] ?? 'Not issued'; ?></small>
                                </div>
                            </div>
                            <?php echo getStatusBadge($cert['status']); ?>
                        </div>
                        <p class="small text-muted mb-2">
                            <strong><?php echo getTranslation('name'); ?>:</strong> <?php echo htmlspecialchars($cert['title']); ?>
                        </p>
                        <p class="small text-muted mb-3">
                            <strong><?php echo getTranslation('issued_date'); ?>:</strong> <?php echo $cert['approved_at'] ? date('F d, Y', strtotime($cert['approved_at'])) : 'Not yet'; ?>
                        </p>
                        <div class="d-flex gap-2">
                            <?php if ($cert['status'] == 'approved'): ?>
                                <button class="btn btn-sm btn-outline-primary flex-fill" onclick="viewCertificate(<?php echo $cert['id']; ?>, '<?php echo $cert['type']; ?>')">
                                    <i class="bi bi-eye"></i> Preview
                                </button>
                                <a href="download_certificate.php?id=<?php echo $cert['id']; ?>&type=<?php echo $cert['type']; ?>&format=jpg" class="btn btn-sm btn-outline-secondary flex-fill">
                                    <i class="bi bi-image"></i> JPG
                                </a>
                              
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-secondary flex-fill" disabled>
                                    <i class="bi bi-eye"></i> Preview
                                </button>
                                <button class="btn btn-sm btn-outline-secondary flex-fill" disabled>
                                    <i class="bi bi-image"></i> JPG
                                </button>
                           
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-4 p-4 text-center shadow-sm">
            <i class="bi bi-file-text fs-1 text-muted"></i>
            <p class="text-muted mt-2"><?php echo getTranslation('no_certificates'); ?></p>
            <a href="dashboard.php" class="btn btn-primary-custom mt-2"><?php echo getTranslation('register_now'); ?></a>
        </div>
    <?php endif; ?>
</div>

<!-- Certificate Preview Modal -->
<div class="modal fade" id="certificatePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-text"></i> Certificate Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="certificatePreviewContent">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Photo Upload Modal -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-camera"></i> <?php echo getTranslation('upload_photo'); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="upload_photo" value="1">
                    <div class="text-center mb-3">
                        <img id="previewImage" src="<?php echo $profile_image; ?>" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #005dac;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold"><?php echo getTranslation('choose_photo'); ?></label>
                        <input type="file" name="profile_photo" class="form-control" accept="image/*" required onchange="previewPhoto(this)">
                    </div>
                    <small class="text-muted"><?php echo getTranslation('photo_format'); ?></small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo getTranslation('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary-custom"><?php echo getTranslation('upload'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function viewCertificate(id, type) {
    const modal = new bootstrap.Modal(document.getElementById('certificatePreviewModal'));
    const contentDiv = document.getElementById('certificatePreviewContent');
    
    contentDiv.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
    modal.show();
    
    fetch('preview_certificate.php?id=' + id + '&type=' + type)
        .then(response => response.text())
        .then(html => {
            contentDiv.innerHTML = html;
        })
        .catch(error => {
            contentDiv.innerHTML = '<div class="alert alert-danger">Failed to load certificate preview</div>';
        });
}
</script>

<?php include 'includes/footer.php'; ?>
<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login_simple.php');
    exit();
}

$user = getUserById($_SESSION['user_id']);
$birth_count = getUserRegistrationsCount($_SESSION['user_id'], 'birth');
$marriage_count = getUserRegistrationsCount($_SESSION['user_id'], 'marriage');
$divorce_count = getUserRegistrationsCount($_SESSION['user_id'], 'divorce');
$death_count = getUserRegistrationsCount($_SESSION['user_id'], 'death');

include 'includes/header.php';
?>

<div class="container">
    <!-- Welcome Banner -->
    <div class="bg-primary-gradient text-white p-4 rounded-4 mb-4">
        <h2 class="fw-bold mb-1"><?php echo getTranslation('welcome_back'); ?></h2>
        <p class="opacity-90 mb-0"><?php echo htmlspecialchars($user['full_name']); ?></p>
    </div>

    <!-- Statistics Row -->
    <div class="row g-3 my-5">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo $birth_count; ?></div>
                <small class="text-muted"><?php echo getTranslation('birth_registration'); ?></small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo $marriage_count; ?></div>
                <small class="text-muted"><?php echo getTranslation('marriage_registration'); ?></small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo $divorce_count; ?></div>
                <small class="text-muted"><?php echo getTranslation('divorce_registration'); ?></small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo $death_count; ?></div>
                <small class="text-muted"><?php echo getTranslation('death_registration'); ?></small>
            </div>
        </div>
    </div>

    <!-- Services Cards -->
    <h3 class="fw-bold mb-3"><?php echo getTranslation('services_title'); ?></h3>
    <div class="services-grid mb-5">
        <div class="service-card" data-service="birth">
            <div class="service-img">
                <img src="assets/images/birth/birth.jpeg" alt="Birth" onerror="this.src='https://placehold.co/400x400/005dac/white?text=Birth'">
            </div>
            <h5 class="fw-bold mt-2"><?php echo getTranslation('birth_registration'); ?></h5>
            <small class="text-muted"><?php echo getTranslation('register_new_birth'); ?></small>
        </div>
        <div class="service-card" data-service="marriage">
            <div class="service-img">
                <img src="assets/images/marriage/mariage.jpeg" alt="Marriage" onerror="this.src='https://placehold.co/400x400/005dac/white?text=Marriage'">
            </div>
            <h5 class="fw-bold mt-2"><?php echo getTranslation('marriage_registration'); ?></h5>
            <small class="text-muted"><?php echo getTranslation('register_marriage'); ?></small>
        </div>
        <div class="service-card" data-service="divorce">
            <div class="service-img">
                <img src="assets/images/divorce/divorce.jpg" alt="Divorce" onerror="this.src='https://placehold.co/400x400/005dac/white?text=Divorce'">
            </div>
            <h5 class="fw-bold mt-2"><?php echo getTranslation('divorce_registration'); ?></h5>
            <small class="text-muted"><?php echo getTranslation('register_divorce'); ?></small>
        </div>
        <div class="service-card" data-service="death">
            <div class="service-img">
                <img src="assets/images/death/death.jpeg" alt="Death" onerror="this.src='https://placehold.co/400x400/005dac/white?text=Death'">
            </div>
            <h5 class="fw-bold mt-2"><?php echo getTranslation('death_registration'); ?></h5>
            <small class="text-muted"><?php echo getTranslation('register_death'); ?></small>
        </div>
    </div>

    <!-- Recent Registrations -->
    <h3 class="fw-bold mb-3"><?php echo getTranslation('recent_title'); ?></h3>
    <div class="bg-white rounded-4 p-3 shadow-sm">
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="fw-semibold"><?php echo getTranslation('col_name'); ?></span>
            <span class="fw-semibold"><?php echo getTranslation('col_type'); ?></span>
            <span class="fw-semibold"><?php echo getTranslation('col_date'); ?></span>
        </div>
        <?php
        $recent_sql = "SELECT 'birth' as type, child_name as name, submitted_at FROM birth_registrations WHERE user_id = ? 
                       UNION ALL 
                       SELECT 'marriage' as type, CONCAT(groom_name, ' & ', bride_name) as name, submitted_at FROM marriage_registrations WHERE user_id = ?
                       UNION ALL 
                       SELECT 'divorce' as type, CONCAT(husband_name, ' & ', wife_name) as name, submitted_at FROM divorce_registrations WHERE user_id = ?
                       UNION ALL 
                       SELECT 'death' as type, deceased_name as name, submitted_at FROM death_registrations WHERE user_id = ?
                       ORDER BY submitted_at DESC LIMIT 5";
        $stmt = mysqli_prepare($conn, $recent_sql);
        mysqli_stmt_bind_param($stmt, "iiii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        $recent_result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($recent_result) > 0):
            while ($row = mysqli_fetch_assoc($recent_result)):
                $type_class = $row['type'] == 'birth' ? 'text-primary' : ($row['type'] == 'marriage' ? 'text-success' : 'text-danger');
        ?>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span><?php echo htmlspecialchars($row['name']); ?></span>
                    <span class="<?php echo $type_class; ?>"><?php echo ucfirst($row['type']); ?></span>
                    <span class="text-muted small"><?php echo date('M d, Y', strtotime($row['submitted_at'])); ?></span>
                </div>
            <?php
            endwhile;
        else:
            ?>
            <div class="text-center py-3 text-muted"><?php echo getTranslation('no_registrations'); ?></div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================ -->
<!-- BIRTH REGISTRATION MODAL - WITH PHOTO UPLOAD -->
<!-- ============================================ -->
<div class="modal fade" id="birthModal" tabindex="-1" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">📝 <?php echo getTranslation('birth_registration'); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="process/submit_birth.php" method="POST" enctype="multipart/form-data" id="birthForm">

                    <!-- Child Photo Upload (3x4) -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">📷 <?php echo getTranslation('child_photo'); ?> (3x4)</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('upload_photo'); ?></label>
                                    <input type="file" name="child_photo" class="form-control" accept="image/jpeg,image/png,image/jpg" onchange="previewPhoto(this, 'birthPreview')">
                                    <small class="text-muted"><?php echo getTranslation('photo_format'); ?> (JPG, PNG) - 3x4 cm</small>
                                </div>
                                <div class="col-md-6 mb-3 text-center">
                                    <div id="birthPreviewContainer" style="display: none;">
                                        <img id="birthPreview" src="#" alt="Child Preview" style="width: 120px; height: 160px; object-fit: cover; border: 2px solid #005dac; border-radius: 5px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Child Information -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">👶 <?php echo getTranslation('child_info'); ?></h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('child_name'); ?> *</label>
                                    <input type="text" name="child_name" class="form-control form-control-custom" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('father_name'); ?> *</label>
                                    <input type="text" name="child_father_name" class="form-control form-control-custom" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('grandfather_name'); ?></label>
                                    <input type="text" name="child_grandfather_name" class="form-control form-control-custom">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Birth Details with Sex Radio Buttons -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">📅 <?php echo getTranslation('birth_details'); ?></h6>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('gender'); ?> *</label><br>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" value="Male" id="maleRadio" checked>
                                        <label class="form-check-label" for="maleRadio"><?php echo getTranslation('male'); ?></label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" value="Female" id="femaleRadio">
                                        <label class="form-check-label" for="femaleRadio"><?php echo getTranslation('female'); ?></label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('birth_month'); ?> *</label>
                                    <select name="birth_month" class="form-select form-control-custom" required>
                                        <option value=""><?php echo getTranslation('select_month'); ?></option>
                                        <option value="January">January / ጥር</option>
                                        <option value="February">February / የካቲት</option>
                                        <option value="March">March / መጋቢት</option>
                                        <option value="April">April / ሚያዝያ</option>
                                        <option value="May">May / ግንቦት</option>
                                        <option value="June">June / ሰኔ</option>
                                        <option value="July">July / ሐምሌ</option>
                                        <option value="August">August / ነሐሴ</option>
                                        <option value="September">September / መስከረም</option>
                                        <option value="October">October / ጥቅምት</option>
                                        <option value="November">November / ህዳር</option>
                                        <option value="December">December / ታህሳስ</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('birth_date'); ?> *</label>
                                    <input type="number" name="birth_day" class="form-control form-control-custom" min="1" max="31" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('birth_year'); ?> *</label>
                                    <input type="number" name="birth_year" class="form-control form-control-custom" min="1900" max="2026" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Place of Birth -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">📍 <?php echo getTranslation('place_of_birth'); ?></h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('birth_place'); ?> (<?php echo getTranslation('kebele'); ?>) *</label>
                                    <input type="text" name="birth_place" class="form-control form-control-custom" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('region_admin'); ?></label>
                                    <select name="region_admin" class="form-select form-control-custom">
                                        <option value=""><?php echo getTranslation('select_region'); ?></option>
                                        <option value="Tigray">Tigray / ትግራይ</option>
                                        <option value="Afar">Afar / አፋር</option>
                                        <option value="Amhara">Amhara / አማራ</option>
                                        <option value="Oromia">Oromia / ኦሮሚያ</option>
                                        <option value="Somali">Somali / ሶማሊ</option>
                                        <option value="Benishangul-Gumuz">Benishangul-Gumuz / ቤንሻንጉል-ጉሙዝ</option>
                                        <option value="Central Ethiopia">Central Ethiopia / ማዕከላዊ ኢትዮጵያ</option>
                                        <option value="South West Ethiopia">South West Ethiopia / ደቡብ ምዕራብ ኢትዮጵያ</option>
                                        <option value="South Ethiopia">South Ethiopia / ደቡብ ኢትዮጵያ</option>
                                        <option value="Gambela">Gambela / ጋምቤላ</option>
                                        <option value="Harari">Harari / ሐረሪ</option>
                                        <option value="Addis Ababa">Addis Ababa / አዲስ አበባ</option>
                                        <option value="Dire Dawa">Dire Dawa / ድሬዳዋ</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('woreda'); ?></label>
                                    <input type="text" name="woreda" class="form-control form-control-custom">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('nationality'); ?></label>
                                    <select name="birth_nationality" class="form-select form-control-custom">
                                        <option value="Ethiopian">ኢትዮጵያዊ / Ethiopian</option>
                                        <option value="American">አሜሪካዊ / American</option>
                                        <option value="British">ብሪቲሽ / British</option>
                                        <option value="Canadian">ካናዳዊ / Canadian</option>
                                        <option value="German">ጀርመናዊ / German</option>
                                        <option value="French">ፈረንሳዊ / French</option>
                                        <option value="Italian">ጣሊያናዊ / Italian</option>
                                        <option value="Chinese">ቻይናዊ / Chinese</option>
                                        <option value="Indian">ህንዳዊ / Indian</option>
                                        <option value="Other">ሌላ / Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Parents Information -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">👪 <?php echo getTranslation('parents_info'); ?></h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('mother_name'); ?> *</label>
                                    <input type="text" name="mother_name" class="form-control form-control-custom" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('mother_nationality'); ?></label>
                                    <select name="mother_nationality" class="form-select form-control-custom">
                                        <option value="Ethiopian">ኢትዮጵያዊ / Ethiopian</option>
                                        <option value="American">አሜሪካዊ / American</option>
                                        <option value="British">ብሪቲሽ / British</option>
                                        <option value="Canadian">ካናዳዊ / Canadian</option>
                                        <option value="German">ጀርመናዊ / German</option>
                                        <option value="French">ፈረንሳዊ / French</option>
                                        <option value="Italian">ጣሊያናዊ / Italian</option>
                                        <option value="Chinese">ቻይናዊ / Chinese</option>
                                        <option value="Indian">ህንዳዊ / Indian</option>
                                        <option value="Other">ሌላ / Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('father_name'); ?> *</label>
                                    <input type="text" name="father_name" class="form-control form-control-custom" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('father_nationality'); ?></label>
                                    <select name="father_nationality" class="form-select form-control-custom">
                                        <option value="Ethiopian">ኢትዮጵያዊ / Ethiopian</option>
                                        <option value="American">አሜሪካዊ / American</option>
                                        <option value="British">ብሪቲሽ / British</option>
                                        <option value="Canadian">ካናዳዊ / Canadian</option>
                                        <option value="German">ጀርመናዊ / German</option>
                                        <option value="French">ፈረንሳዊ / French</option>
                                        <option value="Italian">ጣሊያናዊ / Italian</option>
                                        <option value="Chinese">ቻይናዊ / Chinese</option>
                                        <option value="Indian">ህንዳዊ / Indian</option>
                                        <option value="Other">ሌላ / Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo getTranslation('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary-custom"><?php echo getTranslation('submit'); ?></button>
                    </div>
                    <div id="birthFormMessage" class="message mt-3"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MARRIAGE REGISTRATION MODAL - WITH SEPARATE PHOTOS FOR HUSBAND & WIFE -->
<!-- ============================================ -->
<div class="modal fade" id="marriageModal" tabindex="-1" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">💍 <?php echo getTranslation('Marriage Registration Form'); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="process/submit_marriage.php" method="POST" enctype="multipart/form-data" id="marriageForm">

                    <!-- ============================================ -->
                    <!-- SEPARATE PHOTOS FOR HUSBAND AND WIFE -->
                    <!-- ============================================ -->
                    <div class="row mb-3">
                        <!-- Husband Photo -->
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">📷 <?php echo getTranslation('Husband Photo'); ?> (3x4)</h6>
                                    <div class="row">
                                        <div class="col-md-7 mb-3">
                                            <label class="form-label fw-semibold"><?php echo getTranslation('upload photo'); ?></label>
                                            <input type="file" name="husband_photo" class="form-control" accept="image/jpeg,image/png,image/jpg" onchange="previewPhoto(this, 'husbandPreview')">
                                            <small class="text-muted"><?php echo getTranslation('photo_format'); ?> (JPG, PNG) - 3x4</small>
                                        </div>
                                        <div class="col-md-5 mb-3 text-center">
                                            <div id="husbandPreviewContainer" style="display: none;">
                                                <img id="husbandPreview" src="#" alt="Husband Preview" style="width: 100px; height: 130px; object-fit: cover; border: 2px solid #005dac; border-radius: 5px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Wife Photo -->
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">📷 <?php echo getTranslation('wife_photo'); ?> (3x4)</h6>
                                    <div class="row">
                                        <div class="col-md-7 mb-3">
                                            <label class="form-label fw-semibold"><?php echo getTranslation('upload photo'); ?></label>
                                            <input type="file" name="wife_photo" class="form-control" accept="image/jpeg,image/png,image/jpg" onchange="previewPhoto(this, 'wifePreview')">
                                            <small class="text-muted"><?php echo getTranslation('photo_format'); ?> (JPG, PNG) - 3x4 cm</small>
                                        </div>
                                        <div class="col-md-5 mb-3 text-center">
                                            <div id="wifePreviewContainer" style="display: none;">
                                                <img id="wifePreview" src="#" alt="Wife Preview" style="width: 100px; height: 130px; object-fit: cover; border: 2px solid #005dac; border-radius: 5px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- UNIQUE ID NUMBERS -->
                    <!-- ============================================ -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">📋 <?php echo getTranslation('husband_birth_registration_unique_id'); ?></label>
                            <input type="text" name="husband_birth_registration_unique_id" class="form-control form-control-custom" placeholder="e.g., BIR-2024-001">
                            <small class="text-muted">የባል ልዩ የልደት ምዝገባ መለያ ቁጥር</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">🆔 <?php echo getTranslation('wife_birth_registration_unique_id'); ?></label>
                            <input type="text" name="wife_birth_registration_unique_id" class="form-control form-control-custom" placeholder="e.g., BIR-2024-002">
                            <small class="text-muted">የሚስት ልዩ የልደት ምዝገባ መለያ ቁጥር</small>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- HUSBAND AND WIFE INFORMATION TABLE -->
                    <!-- ============================================ -->
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th colspan="4" class="text-center">🤵 <?php echo getTranslation('husband_info'); ?> / የባል መረጃ</th>
                                    <th colspan="4" class="text-center">👰 <?php echo getTranslation('wife_info'); ?> / የሚስት መረጃ</th>
                                </tr>
                                <tr class="table-secondary">
                                    <th><?php echo getTranslation('full_name'); ?> / ሙሉ ስም</th>
                                    <th><?php echo getTranslation('father_name'); ?> / የአባት ስም</th>
                                    <th><?php echo getTranslation('grandfather_name'); ?> / የአያት ስም</th>
                                    <th><?php echo getTranslation('nationality'); ?> / ዜግነት</th>
                                    <th><?php echo getTranslation('full_name'); ?> / ሙሉ ስም</th>
                                    <th><?php echo getTranslation('father_name'); ?> / የአባት ስም</th>
                                    <th><?php echo getTranslation('grandfather_name'); ?> / የአያት ስም</th>
                                    <th><?php echo getTranslation('nationality'); ?> / ዜግነት</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <!-- Husband Information -->
                                    <td><input type="text" name="husband_name" class="form-control" required placeholder="ሙሉ ስም"></td>
                                    <td><input type="text" name="husband_father_name" class="form-control" required placeholder="የአባት ስም"></td>
                                    <td><input type="text" name="husband_grandfather_name" class="form-control" placeholder="የአያት ስም"></td>
                                    <td>
                                        <select name="husband_nationality" class="form-select" required>
                                            <option value="Ethiopian">ኢትዮጵያዊ / Ethiopian</option>
                                            <option value="American">አሜሪካዊ / American</option>
                                            <option value="British">ብሪቲሽ / British</option>
                                            <option value="Canadian">ካናዳዊ / Canadian</option>
                                            <option value="German">ጀርመናዊ / German</option>
                                            <option value="French">ፈረንሳዊ / French</option>
                                            <option value="Italian">ጣሊያናዊ / Italian</option>
                                            <option value="Chinese">ቻይናዊ / Chinese</option>
                                            <option value="Indian">ህንዳዊ / Indian</option>
                                            <option value="Other">ሌላ / Other</option>
                                        </select>
                                    </td>
                                    <!-- Wife Information -->
                                    <td><input type="text" name="wife_name" class="form-control" required placeholder="ሙሉ ስም"></td>
                                    <td><input type="text" name="wife_father_name" class="form-control" required placeholder="የአባት ስም"></td>
                                    <td><input type="text" name="wife_grandfather_name" class="form-control" placeholder="የአያት ስም"></td>
                                    <td>
                                        <select name="wife_nationality" class="form-select" required>
                                            <option value="Ethiopian">ኢትዮጵያዊ / Ethiopian</option>
                                            <option value="American">አሜሪካዊ / American</option>
                                            <option value="British">ብሪቲሽ / British</option>
                                            <option value="Canadian">ካናዳዊ / Canadian</option>
                                            <option value="German">ጀርመናዊ / German</option>
                                            <option value="French">ፈረንሳዊ / French</option>
                                            <option value="Italian">ጣሊያናዊ / Italian</option>
                                            <option value="Chinese">ቻይናዊ / Chinese</option>
                                            <option value="Indian">ህንዳዊ / Indian</option>
                                            <option value="Other">ሌላ / Other</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ============================================ -->
                    <!-- DATE OF BIRTH FOR HUSBAND AND WIFE -->
                    <!-- ============================================ -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">📅 <?php echo getTranslation('husband_dob'); ?> / የባል የትውልድ ቀን</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold"><?php echo getTranslation('month'); ?> / ወር</label>
                                            <select name="husband_birth_month" class="form-select" required>
                                                <option value=""><?php echo getTranslation('select_month'); ?></option>
                                                <option value="January">January / ጥር</option>
                                                <option value="February">February / የካቲት</option>
                                                <option value="March">March / መጋቢት</option>
                                                <option value="April">April / ሚያዝያ</option>
                                                <option value="May">May / ግንቦት</option>
                                                <option value="June">June / ሰኔ</option>
                                                <option value="July">July / ሐምሌ</option>
                                                <option value="August">August / ነሐሴ</option>
                                                <option value="September">September / መስከረም</option>
                                                <option value="October">October / ጥቅምት</option>
                                                <option value="November">November / ህዳር</option>
                                                <option value="December">December / ታህሳስ</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold"><?php echo getTranslation('date'); ?> / ቀን</label>
                                            <input type="number" name="husband_birth_day" class="form-control" min="1" max="31" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold"><?php echo getTranslation('year'); ?> / ዓመት</label>
                                            <input type="number" name="husband_birth_year" class="form-control" min="1900" max="2026" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">📅 <?php echo getTranslation('wife_dob'); ?> / የሚስት የትውልድ ቀን</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold"><?php echo getTranslation('month'); ?> / ወር</label>
                                            <select name="wife_birth_month" class="form-select" required>
                                                <option value=""><?php echo getTranslation('select_month'); ?></option>
                                                <option value="January">January / ጥር</option>
                                                <option value="February">February / የካቲት</option>
                                                <option value="March">March / መጋቢት</option>
                                                <option value="April">April / ሚያዝያ</option>
                                                <option value="May">May / ግንቦት</option>
                                                <option value="June">June / ሰኔ</option>
                                                <option value="July">July / ሐምሌ</option>
                                                <option value="August">August / ነሐሴ</option>
                                                <option value="September">September / መስከረም</option>
                                                <option value="October">October / ጥቅምት</option>
                                                <option value="November">November / ህዳር</option>
                                                <option value="December">December / ታህሳስ</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold"><?php echo getTranslation('date'); ?> / ቀን</label>
                                            <input type="number" name="wife_birth_day" class="form-control" min="1" max="31" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold"><?php echo getTranslation('year'); ?> / ዓመት</label>
                                            <input type="number" name="wife_birth_year" class="form-control" min="1900" max="2026" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- MARRIAGE DATE -->
                    <!-- ============================================ -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">💒 <?php echo getTranslation('marriage_date'); ?> / የጋብቻ ቀን</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('month'); ?> / ወር *</label>
                                    <select name="marriage_month" class="form-select" required>
                                        <option value=""><?php echo getTranslation('select_month'); ?></option>
                                        <option value="January">January / ጥር</option>
                                        <option value="February">February / የካቲት</option>
                                        <option value="March">March / መጋቢት</option>
                                        <option value="April">April / ሚያዝያ</option>
                                        <option value="May">May / ግንቦት</option>
                                        <option value="June">June / ሰኔ</option>
                                        <option value="July">July / ሐምሌ</option>
                                        <option value="August">August / ነሐሴ</option>
                                        <option value="September">September / መስከረም</option>
                                        <option value="October">October / ጥቅምት</option>
                                        <option value="November">November / ህዳር</option>
                                        <option value="December">December / ታህሳስ</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('date'); ?> / ቀን *</label>
                                    <input type="number" name="marriage_day" class="form-control" min="1" max="31" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('year'); ?> / ዓመት *</label>
                                    <input type="number" name="marriage_year" class="form-control" min="1900" max="2026" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- PLACE OF MARRIAGE REGISTRATION -->
                    <!-- ============================================ -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">📍 <?php echo getTranslation('marriage_place_registration'); ?> / ጋብቻው የተፈጸመበት ቦታ</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('region'); ?> / ክልል</label>
                                    <select name="marriage_region" class="form-select">
                                        <option value=""><?php echo getTranslation('select_region'); ?></option>
                                        <option value="Tigray">Tigray / ትግራይ</option>
                                        <option value="Afar">Afar / አፋር</option>
                                        <option value="Amhara">Amhara / አማራ</option>
                                        <option value="Oromia">Oromia / ኦሮሚያ</option>
                                        <option value="Somali">Somali / ሶማሊ</option>
                                        <option value="Benishangul-Gumuz">Benishangul-Gumuz / ቤንሻንጉል-ጉሙዝ</option>
                                        <option value="SNNPR">SNNPR / ደቡብ</option>
                                        <option value="Gambela">Gambela / ጋምቤላ</option>
                                        <option value="Harari">Harari / ሐረሪ</option>
                                        <option value="Addis Ababa">Addis Ababa / አዲስ አበባ</option>
                                        <option value="Dire Dawa">Dire Dawa / ድሬዳዋ</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('zone'); ?> / ዞን</label>
                                    <input type="text" name="marriage_zone" class="form-control" placeholder="e.g., North Gondar">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('city'); ?> / ከተማ</label>
                                    <input type="text" name="marriage_city" class="form-control" placeholder="e.g., Addis Ababa">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- MARRIAGE REGISTRATION DATE -->
                    <!-- ============================================ -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">📅 <?php echo getTranslation('marriage_registration_date'); ?> / ጋብቻው የተመዘገበበት ቀን</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('month'); ?> / ወር</label>
                                    <select name="registration_month" class="form-select">
                                        <option value=""><?php echo getTranslation('select_month'); ?></option>
                                        <option value="January">January / ጥር</option>
                                        <option value="February">February / የካቲት</option>
                                        <option value="March">March / መጋቢት</option>
                                        <option value="April">April / ሚያዝያ</option>
                                        <option value="May">May / ግንቦት</option>
                                        <option value="June">June / ሰኔ</option>
                                        <option value="July">July / ሐምሌ</option>
                                        <option value="August">August / ነሐሴ</option>
                                        <option value="September">September / መስከረም</option>
                                        <option value="October">October / ጥቅምት</option>
                                        <option value="November">November / ህዳር</option>
                                        <option value="December">December / ታህሳስ</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('date'); ?> / ቀን</label>
                                    <input type="number" name="registration_day" class="form-control" min="1" max="31">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('year'); ?> / ዓመት</label>
                                    <input type="number" name="registration_year" class="form-control" min="1900" max="2026">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo getTranslation('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary-custom"><?php echo getTranslation('submit'); ?></button>
                    </div>
                    <div id="marriageFormMessage" class="message mt-3"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- DIVORCE REGISTRATION MODAL - WITH SEPARATE PHOTOS FOR HUSBAND & WIFE -->
<!-- ============================================ -->
<div class="modal fade" id="divorceModal" tabindex="-1" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">💔 <?php echo getTranslation('Divorce Registration Form'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="process/submit_divorce.php" method="POST" enctype="multipart/form-data" id="divorceForm">

                    <!-- ============================================ -->
                    <!-- SEPARATE PHOTOS FOR HUSBAND AND WIFE -->
                    <!-- ============================================ -->
                    <div class="row mb-3">
                        <!-- Husband Photo -->
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">📷 <?php echo getTranslation('Husband Photo'); ?> (3x4)</h6>
                                    <div class="row">
                                        <div class="col-md-7 mb-3">
                                            <label class="form-label fw-semibold"><?php echo getTranslation('upload photo'); ?></label>
                                            <input type="file" name="husband_photo" class="form-control" accept="image/jpeg,image/png,image/jpg" onchange="previewPhotoDivorce(this, 'divorceHusbandPreview')">
                                            <small class="text-muted"><?php echo getTranslation('photo_format'); ?> (JPG, PNG) - 3x4</small>
                                        </div>
                                        <div class="col-md-5 mb-3 text-center">
                                            <div id="divorceHusbandPreviewContainer" style="display: none;">
                                                <img id="divorceHusbandPreview" src="#" alt="Husband Preview" style="width: 100px; height: 130px; object-fit: cover; border: 2px solid #005dac; border-radius: 5px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Wife Photo -->
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">📷 <?php echo getTranslation('wife_photo'); ?> (3x4)</h6>
                                    <div class="row">
                                        <div class="col-md-7 mb-3">
                                            <label class="form-label fw-semibold"><?php echo getTranslation('upload photo'); ?></label>
                                            <input type="file" name="wife_photo" class="form-control" accept="image/jpeg,image/png,image/jpg" onchange="previewPhotoDivorce(this, 'divorceWifePreview')">
                                            <small class="text-muted"><?php echo getTranslation('photo_format'); ?> (JPG, PNG) - 3x4 cm</small>
                                        </div>
                                        <div class="col-md-5 mb-3 text-center">
                                            <div id="divorceWifePreviewContainer" style="display: none;">
                                                <img id="divorceWifePreview" src="#" alt="Wife Preview" style="width: 100px; height: 130px; object-fit: cover; border: 2px solid #005dac; border-radius: 5px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- HUSBAND AND WIFE INFORMATION TABLE -->
                    <!-- ============================================ -->
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th colspan="4" class="text-center">🤵 <?php echo getTranslation('husband_info'); ?> / የባል መረጃ</th>
                                    <th colspan="4" class="text-center">👰 <?php echo getTranslation('wife_info'); ?> / የሚስት መረጃ</th>
                                </tr>
                                <tr class="table-secondary">
                                    <th><?php echo getTranslation('full_name'); ?> / ሙሉ ስም</th>
                                    <th><?php echo getTranslation('father_name'); ?> / የአባት ስም</th>
                                    <th><?php echo getTranslation('grandfather_name'); ?> / የአያት ስም</th>
                                    <th><?php echo getTranslation('nationality'); ?> / ዜግነት</th>
                                    <th><?php echo getTranslation('full_name'); ?> / ሙሉ ስም</th>
                                    <th><?php echo getTranslation('father_name'); ?> / የአባት ስም</th>
                                    <th><?php echo getTranslation('grandfather_name'); ?> / የአያት ስም</th>
                                    <th><?php echo getTranslation('nationality'); ?> / ዜግነት</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <!-- Husband Information -->
                                    <td><input type="text" name="husband_name" class="form-control" required placeholder="ሙሉ ስም"></td>
                                    <td><input type="text" name="husband_father_name" class="form-control" required placeholder="የአባት ስም"></td>
                                    <td><input type="text" name="husband_grandfather_name" class="form-control" placeholder="የአያት ስም"></td>
                                    <td>
                                        <select name="husband_nationality" class="form-select" required>
                                            <option value="Ethiopian">ኢትዮጵያዊ / Ethiopian</option>
                                            <option value="American">አሜሪካዊ / American</option>
                                            <option value="British">ብሪቲሽ / British</option>
                                            <option value="Canadian">ካናዳዊ / Canadian</option>
                                            <option value="German">ጀርመናዊ / German</option>
                                            <option value="French">ፈረንሳዊ / French</option>
                                            <option value="Italian">ጣሊያናዊ / Italian</option>
                                            <option value="Chinese">ቻይናዊ / Chinese</option>
                                            <option value="Indian">ህንዳዊ / Indian</option>
                                            <option value="Other">ሌላ / Other</option>
                                        </select>
                                    </td>
                                    <!-- Wife Information -->
                                    <td><input type="text" name="wife_name" class="form-control" required placeholder="ሙሉ ስም"></td>
                                    <td><input type="text" name="wife_father_name" class="form-control" required placeholder="የአባት ስም"></td>
                                    <td><input type="text" name="wife_grandfather_name" class="form-control" placeholder="የአያት ስም"></td>
                                    <td>
                                        <select name="wife_nationality" class="form-select" required>
                                            <option value="Ethiopian">ኢትዮጵያዊ / Ethiopian</option>
                                            <option value="American">አሜሪካዊ / American</option>
                                            <option value="British">ብሪቲሽ / British</option>
                                            <option value="Canadian">ካናዳዊ / Canadian</option>
                                            <option value="German">ጀርመናዊ / German</option>
                                            <option value="French">ፈረንሳዊ / French</option>
                                            <option value="Italian">ጣሊያናዊ / Italian</option>
                                            <option value="Chinese">ቻይናዊ / Chinese</option>
                                            <option value="Indian">ህንዳዊ / Indian</option>
                                            <option value="Other">ሌላ / Other</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ============================================ -->
                    <!-- DIVORCE DATE -->
                    <!-- ============================================ -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">💔 <?php echo getTranslation('divorce_date'); ?> / የፍቺ ቀን</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('month'); ?> / ወር *</label>
                                    <select name="divorce_month" class="form-select" required>
                                        <option value=""><?php echo getTranslation('select_month'); ?></option>
                                        <option value="January">January / ጥር</option>
                                        <option value="February">February / የካቲት</option>
                                        <option value="March">March / መጋቢት</option>
                                        <option value="April">April / ሚያዝያ</option>
                                        <option value="May">May / ግንቦት</option>
                                        <option value="June">June / ሰኔ</option>
                                        <option value="July">July / ሐምሌ</option>
                                        <option value="August">August / ነሐሴ</option>
                                        <option value="September">September / መስከረም</option>
                                        <option value="October">October / ጥቅምት</option>
                                        <option value="November">November / ህዳር</option>
                                        <option value="December">December / ታህሳስ</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('date'); ?> / ቀን *</label>
                                    <input type="number" name="divorce_day" class="form-control" min="1" max="31" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('year'); ?> / ዓመት *</label>
                                    <input type="number" name="divorce_year" class="form-control" min="1900" max="2026" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- PLACE OF DIVORCE REGISTRATION -->
                    <!-- ============================================ -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">📍 <?php echo getTranslation('divorce_place_registration'); ?> / ፍቺው የተፈጸመበት ቦታ</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('region'); ?> / ክልል</label>
                                    <select name="divorce_region" class="form-select">
                                        <option value=""><?php echo getTranslation('select_region'); ?></option>
                                        <option value="Tigray">Tigray / ትግራይ</option>
                                        <option value="Afar">Afar / አፋር</option>
                                        <option value="Amhara">Amhara / አማራ</option>
                                        <option value="Oromia">Oromia / ኦሮሚያ</option>
                                        <option value="Somali">Somali / ሶማሊ</option>
                                        <option value="Benishangul-Gumuz">Benishangul-Gumuz / ቤንሻንጉል-ጉሙዝ</option>
                                        <option value="SNNPR">SNNPR / ደቡብ</option>
                                        <option value="Gambela">Gambela / ጋምቤላ</option>
                                        <option value="Harari">Harari / ሐረሪ</option>
                                        <option value="Addis Ababa">Addis Ababa / አዲስ አበባ</option>
                                        <option value="Dire Dawa">Dire Dawa / ድሬዳዋ</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('zone'); ?> / ዞን</label>
                                    <input type="text" name="divorce_zone" class="form-control" placeholder="e.g., North Gondar">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('city'); ?> / ከተማ</label>
                                    <input type="text" name="divorce_city" class="form-control" placeholder="e.g., Addis Ababa">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- DIVORCE REGISTRATION DATE -->
                    <!-- ============================================ -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">📅 <?php echo getTranslation('divorce_registration_date_info'); ?> / ፍቺው የተመዘገበበት ቀን</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('month'); ?> / ወር</label>
                                    <select name="divorce_registration_month" class="form-select">
                                        <option value=""><?php echo getTranslation('select_month'); ?></option>
                                        <option value="January">January / ጥር</option>
                                        <option value="February">February / የካቲት</option>
                                        <option value="March">March / መጋቢት</option>
                                        <option value="April">April / ሚያዝያ</option>
                                        <option value="May">May / ግንቦት</option>
                                        <option value="June">June / ሰኔ</option>
                                        <option value="July">July / ሐምሌ</option>
                                        <option value="August">August / ነሐሴ</option>
                                        <option value="September">September / መስከረም</option>
                                        <option value="October">October / ጥቅምት</option>
                                        <option value="November">November / ህዳር</option>
                                        <option value="December">December / ታህሳስ</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('date'); ?> / ቀን</label>
                                    <input type="number" name="divorce_registration_day" class="form-control" min="1" max="31">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('year'); ?> / ዓመት</label>
                                    <input type="number" name="divorce_registration_year" class="form-control" min="1900" max="2026">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Court Name -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">⚖️ <?php echo getTranslation('court_name'); ?> / የፍርድ ቤት ስም</h6>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="court_name" class="form-control" placeholder="e.g., Federal First Instance Court">
                                    <small class="text-muted">የፍርድ ቤት ስም / Court Name</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Decree Number -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">📄 <?php echo getTranslation('decree_number'); ?> / የፍርድ ቤት ውሳኔ ቁጥር</h6>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="decree_number" class="form-control" required placeholder="e.g., DIV-2024-001">
                                    <small class="text-muted">የፍርድ ቤት ውሳኔ ቁጥር / Decree Number</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo getTranslation('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary-custom"><?php echo getTranslation('submit'); ?></button>
                    </div>
                    <div id="divorceFormMessage" class="message mt-3"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- DEATH REGISTRATION MODAL - WITH PHOTO UPLOAD & COMPLETE FIELDS -->
<!-- ============================================ -->
<div class="modal fade" id="deathModal" tabindex="-1" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">🕊️ <?php echo getTranslation('death_registration'); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="process/submit_death.php" method="POST" enctype="multipart/form-data" id="deathForm">

                    <!-- ============================================ -->
                    <!-- DECEASED PHOTO UPLOAD (3x4) -->
                    <!-- ============================================ -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">📷 <?php echo getTranslation('deceased_photo'); ?> (3x4)</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('upload_photo'); ?></label>
                                    <input type="file" name="deceased_photo" class="form-control" accept="image/jpeg,image/png,image/jpg" onchange="previewPhoto(this, 'deathPreview')">
                                    <small class="text-muted"><?php echo getTranslation('photo_format'); ?> (JPG, PNG) - 3x4 cm</small>
                                </div>
                                <div class="col-md-6 mb-3 text-center">
                                    <div id="deathPreviewContainer" style="display: none;">
                                        <img id="deathPreview" src="#" alt="Deceased Preview" style="width: 120px; height: 160px; object-fit: cover; border: 2px solid #005dac; border-radius: 5px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- UNIQUE ID NUMBERS -->
                    <!-- ============================================ -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">📋 <?php echo getTranslation('death_form_number'); ?></label>
                            <input type="text" name="death_form_number" class="form-control form-control-custom" placeholder="e.g., DTH-2024-001">
                            <small class="text-muted">የሞት ክብር መዝገብ ቅጽ ቁጥር</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">🆔 <?php echo getTranslation('death_unique_id'); ?></label>
                            <input type="text" name="death_unique_id" class="form-control form-control-custom" placeholder="e.g., DTH-2024-001">
                            <small class="text-muted">የሞት ምዝገባ ልዩ መለያ ቁጥር</small>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- DECEASED FULL NAME INFORMATION -->
                    <!-- ============================================ -->
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th colspan="3" class="text-center">🪦 <?php echo getTranslation('deceased_info'); ?> / የሟች መረጃ</th>
                                </tr>
                                <tr class="table-secondary">
                                    <th><?php echo getTranslation('full_name'); ?> / ሙሉ ስም</th>
                                    <th><?php echo getTranslation('father_name'); ?> / የአባት ስም</th>
                                    <th><?php echo getTranslation('grandfather_name'); ?> / የአያት ስም</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" name="deceased_name" class="form-control" required placeholder="ሙሉ ስም"></td>
                                    <td><input type="text" name="deceased_father_name" class="form-control" required placeholder="የአባት ስም"></td>
                                    <td><input type="text" name="deceased_grandfather_name" class="form-control" placeholder="የአያት ስም"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ============================================ -->
                    <!-- PERSONAL DETAILS (Title, Sex, Nationality) -->
                    <!-- ============================================ -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">🎖️ <?php echo getTranslation('title'); ?> / ማዕረግ</h6>
                                    <select name="title" class="form-select">
                                        <option value=""><?php echo getTranslation('select_title'); ?></option>
                                        <option value="Mr">አቶ / Mr.</option>
                                        <option value="Mrs">ወ/ሮ / Mrs.</option>
                                        <option value="Miss">ወ/ሪት / Miss</option>
                                        <option value="Dr">ዶ/ር / Dr.</option>
                                        <option value="Prof">ፕሮፌሰር / Prof.</option>
                                        <option value="Engineer">መሐንዲስ / Engineer</option>
                                        <option value="Lawyer">ጠበቃ / Lawyer</option>
                                        <option value="Other">ሌላ / Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">⚥ <?php echo getTranslation('sex'); ?> / ጾታ</h6>
                                    <select name="sex" class="form-select" required>
                                        <option value=""><?php echo getTranslation('select_sex'); ?></option>
                                        <option value="Male">ወንድ / Male</option>
                                        <option value="Female">ሴት / Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">🌍 <?php echo getTranslation('nationality'); ?> / ዜግነት</h6>
                                    <select name="nationality" class="form-select" required>
                                        <option value="Ethiopian">ኢትዮጵያዊ / Ethiopian</option>
                                        <option value="American">አሜሪካዊ / American</option>
                                        <option value="British">ብሪቲሽ / British</option>
                                        <option value="Canadian">ካናዳዊ / Canadian</option>
                                        <option value="German">ጀርመናዊ / German</option>
                                        <option value="French">ፈረንሳዊ / French</option>
                                        <option value="Italian">ጣሊያናዊ / Italian</option>
                                        <option value="Chinese">ቻይናዊ / Chinese</option>
                                        <option value="Indian">ህንዳዊ / Indian</option>
                                        <option value="Other">ሌላ / Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- DATE OF BIRTH (Optional but good to have) -->
                    <!-- ============================================ -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">📅 <?php echo getTranslation('date_of_birth'); ?> / የትውልድ ቀን (ካለ)</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('month'); ?> / ወር</label>
                                    <select name="dob_month" class="form-select">
                                        <option value=""><?php echo getTranslation('select_month'); ?></option>
                                        <option value="January">January / ጥር</option>
                                        <option value="February">February / የካቲት</option>
                                        <option value="March">March / መጋቢት</option>
                                        <option value="April">April / ሚያዝያ</option>
                                        <option value="May">May / ግንቦት</option>
                                        <option value="June">June / ሰኔ</option>
                                        <option value="July">July / ሐምሌ</option>
                                        <option value="August">August / ነሐሴ</option>
                                        <option value="September">September / መስከረም</option>
                                        <option value="October">October / ጥቅምት</option>
                                        <option value="November">November / ህዳር</option>
                                        <option value="December">December / ታህሳስ</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('date'); ?> / ቀን</label>
                                    <input type="number" name="dob_day" class="form-control" min="1" max="31">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('year'); ?> / ዓመት</label>
                                    <input type="number" name="dob_year" class="form-control" min="1900" max="2026">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- PLACE OF DEATH -->
                    <!-- ============================================ -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">📍 <?php echo getTranslation('place_of_death'); ?> / የሞት ቦታ</h6>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="place_of_death" class="form-control" required placeholder="e.g., Tikur Anbessa Hospital, Addis Ababa">
                                    <small class="text-muted">ሆስፒታል፣ ቤት፣ ወይም ሌላ ቦታ</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- DATE OF DEATH (Single) -->
                    <!-- ============================================ -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">📅 <?php echo getTranslation('date_of_death'); ?> / የሞት ቀን</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('month'); ?> / ወር *</label>
                                    <select name="death_month" class="form-select" required>
                                        <option value=""><?php echo getTranslation('select_month'); ?></option>
                                        <option value="January">January / ጥር</option>
                                        <option value="February">February / የካቲት</option>
                                        <option value="March">March / መጋቢት</option>
                                        <option value="April">April / ሚያዝያ</option>
                                        <option value="May">May / ግንቦት</option>
                                        <option value="June">June / ሰኔ</option>
                                        <option value="July">July / ሐምሌ</option>
                                        <option value="August">August / ነሐሴ</option>
                                        <option value="September">September / መስከረም</option>
                                        <option value="October">October / ጥቅምት</option>
                                        <option value="November">November / ህዳር</option>
                                        <option value="December">December / ታህሳስ</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('date'); ?> / ቀን *</label>
                                    <input type="number" name="death_day" class="form-control" min="1" max="31" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('year'); ?> / ዓመት *</label>
                                    <input type="number" name="death_year" class="form-control" min="1900" max="2026" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- CAUSE OF DEATH -->
                    <!-- ============================================ -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">⚠️ <?php echo getTranslation('cause_of_death'); ?> / የሞት ምክንያት</h6>
                            <div class="row">
                                <div class="col-md-12">
                                    <textarea name="cause_of_death" class="form-control" rows="2" placeholder="e.g., Heart Attack, Accident, Illness, etc."></textarea>
                                    <small class="text-muted">የሞት ምክንያት (ካረጋገጠ)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- DEATH REGISTRATION DATE (Single) -->
                    <!-- ============================================ -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">📅 <?php echo getTranslation('death_registration_date'); ?> / ሞቱ የተመዘገበበት ቀን</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('month'); ?> / ወር</label>
                                    <select name="death_registration_month" class="form-select">
                                        <option value=""><?php echo getTranslation('select_month'); ?></option>
                                        <option value="January">January / ጥር</option>
                                        <option value="February">February / የካቲት</option>
                                        <option value="March">March / መጋቢት</option>
                                        <option value="April">April / ሚያዝያ</option>
                                        <option value="May">May / ግንቦት</option>
                                        <option value="June">June / ሰኔ</option>
                                        <option value="July">July / ሐምሌ</option>
                                        <option value="August">August / ነሐሴ</option>
                                        <option value="September">September / መስከረም</option>
                                        <option value="October">October / ጥቅምት</option>
                                        <option value="November">November / ህዳር</option>
                                        <option value="December">December / ታህሳስ</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('date'); ?> / ቀን</label>
                                    <input type="number" name="death_registration_day" class="form-control" min="1" max="31">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><?php echo getTranslation('year'); ?> / ዓመት</label>
                                    <input type="number" name="death_registration_year" class="form-control" min="1900" max="2026">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- CERTIFICATE NUMBER (Medical Certificate) -->
                    <!-- ============================================ -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">📋 <?php echo getTranslation('certificate_number'); ?> / የሕክምና ምስክር ወረቀት ቁጥር</h6>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="certificate_number" class="form-control" required placeholder="e.g., MED-DTH-2024-001">
                                    <small class="text-muted">ከሆስፒታል የተሰጠ የሕክምና ምስክር ወረቀት ቁጥር</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo getTranslation('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary-custom"><?php echo getTranslation('submit'); ?></button>
                    </div>
                    <div id="deathFormMessage" class="message mt-3"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Image preview function
    function previewPhoto(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const container = document.getElementById(previewId + 'Container');
                const preview = document.getElementById(previewId);
                if (container && preview) {
                    preview.src = e.target.result;
                    container.style.display = 'block';
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Service cards click handler
        document.querySelectorAll('.service-card').forEach(card => {
            card.addEventListener('click', function() {
                const service = this.getAttribute('data-service');
                if (service === 'birth') new bootstrap.Modal(document.getElementById('birthModal')).show();
                else if (service === 'marriage') new bootstrap.Modal(document.getElementById('marriageModal')).show();
                else if (service === 'divorce') new bootstrap.Modal(document.getElementById('divorceModal')).show();
                else if (service === 'death') new bootstrap.Modal(document.getElementById('deathModal')).show();
            });
        });

        // Navigation links click handler
        const navBirth = document.getElementById('navBirth');
        const navMarriage = document.getElementById('navMarriage');
        const navDivorce = document.getElementById('navDivorce');
        const navDeath = document.getElementById('navDeath');

        if (navBirth) navBirth.addEventListener('click', function(e) {
            e.preventDefault();
            new bootstrap.Modal(document.getElementById('birthModal')).show();
        });
        if (navMarriage) navMarriage.addEventListener('click', function(e) {
            e.preventDefault();
            new bootstrap.Modal(document.getElementById('marriageModal')).show();
        });
        if (navDivorce) navDivorce.addEventListener('click', function(e) {
            e.preventDefault();
            new bootstrap.Modal(document.getElementById('divorceModal')).show();
        });
        if (navDeath) navDeath.addEventListener('click', function(e) {
            e.preventDefault();
            new bootstrap.Modal(document.getElementById('deathModal')).show();
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
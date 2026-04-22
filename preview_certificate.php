<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    exit('<div class="alert alert-danger">Unauthorized</div>');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

$table = $type . '_registrations';
$sql = "SELECT * FROM $table WHERE id = ? AND user_id = ? AND status = 'approved'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    exit('<div class="alert alert-danger">Certificate not found</div>');
}

// ============================================
// HELPER FUNCTIONS
// ============================================

function getFullName($type, $data)
{
    switch ($type) {
        case 'birth':
            $name = $data['child_name'] ?? '';
            $father = $data['child_father_name'] ?? $data['father_name'] ?? '';
            $grandfather = $data['child_grandfather_name'] ?? '';
            $fullName = $name;
            if ($father) $fullName .= ' ' . $father;
            if ($grandfather) $fullName .= ' ' . $grandfather;
            return !empty($fullName) ? trim($fullName) : 'N/A';
        case 'marriage':
            $groom = $data['husband_name'] ?? $data['groom_name'] ?? '';
            $bride = $data['wife_name'] ?? $data['bride_name'] ?? '';
            return ($groom ?: 'N/A') . ' and ' . ($bride ?: 'N/A');
        case 'divorce':
            $husband = $data['divorcer_name'] ?? $data['husband_name'] ?? '';
            $wife = $data['divorcee_name'] ?? $data['wife_name'] ?? '';
            return ($husband ?: 'N/A') . ' and ' . ($wife ?: 'N/A');
        case 'death':
            return $data['deceased_name'] ?? 'N/A';
        default:
            return 'N/A';
    }
}

function getFatherName($type, $data)
{
    switch ($type) {
        case 'birth':
            return $data['father_name'] ?? $data['child_father_name'] ?? 'N/A';
        case 'marriage':
            return $data['husband_father_name'] ?? 'N/A';
        case 'divorce':
            return $data['divorcer_father_name'] ?? 'N/A';
        case 'death':
            return $data['deceased_father_name'] ?? 'N/A';
        default:
            return 'N/A';
    }
}

function getGrandFatherName($type, $data)
{
    switch ($type) {
        case 'birth':
            return $data['child_grandfather_name'] ?? 'N/A';
        case 'marriage':
            return $data['husband_grandfather_name'] ?? 'N/A';
        case 'divorce':
            return $data['divorcer_grandfather_name'] ?? 'N/A';
        case 'death':
            return $data['deceased_grandfather_name'] ?? 'N/A';
        default:
            return 'N/A';
    }
}

function getMotherName($type, $data)
{
    switch ($type) {
        case 'birth':
            return $data['mother_name'] ?? 'N/A';
        case 'marriage':
            return $data['wife_name'] ?? $data['bride_name'] ?? 'N/A';
        case 'divorce':
            return $data['divorcee_name'] ?? $data['wife_name'] ?? 'N/A';
        case 'death':
            return 'N/A';
        default:
            return 'N/A';
    }
}

function getSpouseName($type, $data)
{
    switch ($type) {
        case 'marriage':
            return $data['wife_name'] ?? $data['bride_name'] ?? 'N/A';
        case 'divorce':
            return $data['divorcee_name'] ?? $data['wife_name'] ?? 'N/A';
        default:
            return 'N/A';
    }
}

function getGender($type, $data)
{
    switch ($type) {
        case 'birth':
            return $data['gender'] ?? 'N/A';
        case 'death':
            return $data['sex'] ?? $data['gender'] ?? 'N/A';
        default:
            return 'N/A';
    }
}

function getTitle($data)
{
    return $data['title'] ?? 'N/A';
}

function getEventDate($type, $data)
{
    switch ($type) {
        case 'birth':
            if (isset($data['birth_date']) && $data['birth_date']) return date('F d, Y', strtotime($data['birth_date']));
            $month = $data['birth_month'] ?? '';
            $day = $data['birth_day'] ?? '';
            $year = $data['birth_year'] ?? '';
            return ($month && $day && $year) ? $month . ' ' . $day . ', ' . $year : 'N/A';
        case 'marriage':
            if (isset($data['marriage_date']) && $data['marriage_date']) return date('F d, Y', strtotime($data['marriage_date']));
            $month = $data['marriage_month'] ?? '';
            $day = $data['marriage_day'] ?? '';
            $year = $data['marriage_year'] ?? '';
            return ($month && $day && $year) ? $month . ' ' . $day . ', ' . $year : 'N/A';
        case 'divorce':
            if (isset($data['divorce_date']) && $data['divorce_date']) return date('F d, Y', strtotime($data['divorce_date']));
            $month = $data['divorce_month'] ?? '';
            $day = $data['divorce_day'] ?? '';
            $year = $data['divorce_year'] ?? '';
            return ($month && $day && $year) ? $month . ' ' . $day . ', ' . $year : 'N/A';
        case 'death':
            if (isset($data['date_of_death']) && $data['date_of_death']) return date('F d, Y', strtotime($data['date_of_death']));
            if (isset($data['death_date']) && $data['death_date']) return date('F d, Y', strtotime($data['death_date']));
            $month = $data['death_month'] ?? '';
            $day = $data['death_day'] ?? '';
            $year = $data['death_year'] ?? '';
            return ($month && $day && $year) ? $month . ' ' . $day . ', ' . $year : 'N/A';
        default:
            return 'N/A';
    }
}

function getEventPlace($type, $data)
{
    switch ($type) {
        case 'birth':
            return $data['birth_place'] ?? 'N/A';
        case 'marriage':
            return $data['marriage_city'] ?? $data['marriage_place'] ?? $data['city'] ?? 'N/A';
        case 'divorce':
            return $data['divorce_city'] ?? $data['divorce_place'] ?? $data['court_name'] ?? 'N/A';
        case 'death':
            return $data['place_of_death'] ?? $data['death_place'] ?? 'N/A';
        default:
            return 'N/A';
    }
}

function getNationality($type, $data)
{
    switch ($type) {
        case 'birth':
            return $data['nationality'] ?? $data['birth_nationality'] ?? 'Ethiopian';
        case 'marriage':
            return $data['husband_nationality'] ?? 'Ethiopian';
        case 'divorce':
            return $data['divorcer_nationality'] ?? 'Ethiopian';
        case 'death':
            return $data['nationality'] ?? 'Ethiopian';
        default:
            return 'Ethiopian';
    }
}

function getSpouseNationality($type, $data)
{
    switch ($type) {
        case 'marriage':
            return $data['wife_nationality'] ?? 'Ethiopian';
        case 'divorce':
            return $data['divorcee_nationality'] ?? 'Ethiopian';
        default:
            return 'N/A';
    }
}

function getRegistrationDate($data)
{
    if (isset($data['registration_date']) && $data['registration_date']) {
        return date('F d, Y', strtotime($data['registration_date']));
    }
    $month = $data['registration_month'] ?? '';
    $day = $data['registration_day'] ?? '';
    $year = $data['registration_year'] ?? '';
    if ($month && $day && $year) {
        return $month . ' ' . $day . ', ' . $year;
    }
    return date('F d, Y', strtotime($data['submitted_at']));
}

function getApprovedDate($data)
{
    return date('F d, Y', strtotime($data['approved_at']));
}

function getDirectorGeneralName($data)
{
    if (isset($data['registrar_full_name']) && !empty($data['registrar_full_name'])) {
        return $data['registrar_full_name'];
    }
    if (isset($data['registrar_name']) && !empty($data['registrar_name'])) {
        return $data['registrar_name'];
    }
    return '_________________________';
}

function getRegistrarSignature($data)
{
    if (isset($data['registrar_signature']) && !empty($data['registrar_signature'])) {
        return $data['registrar_signature'];
    }
    return '';
}

function getPhoto($type, $data)
{
    switch ($type) {
        case 'birth':
            return $data['child_photo'] ?? '';
        case 'marriage':
            return [
                'husband' => $data['husband_photo'] ?? '',
                'wife' => $data['wife_photo'] ?? ''
            ];
        case 'divorce':
            return [
                'husband' => $data['husband_photo'] ?? '',
                'wife' => $data['wife_photo'] ?? ''
            ];
        case 'death':
            return $data['deceased_photo'] ?? '';
        default:
            return '';
    }
}

function getPhotoPath($photo)
{
    if (empty($photo)) return '';
    $paths = [
        '../' . $photo,
        $photo,
        '../assets/uploads/birth_photos/' . basename($photo),
        'assets/uploads/birth_photos/' . basename($photo),
        '../assets/uploads/marriage_photos/' . basename($photo),
        'assets/uploads/marriage_photos/' . basename($photo),
        '../assets/uploads/divorce_photos/' . basename($photo),
        'assets/uploads/divorce_photos/' . basename($photo),
        '../assets/uploads/death_photos/' . basename($photo),
        'assets/uploads/death_photos/' . basename($photo)
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) return $path;
    }
    return '';
}

function getEthiopianDate($date)
{
    return date('d/m/Y', strtotime($date));
}

function getCertificateTitle($type)
{
    switch ($type) {
        case 'birth':
            return 'የልደት ምስክር ወረቀት / BIRTH CERTIFICATE';
        case 'marriage':
            return 'የጋብቻ ምስክር ወረቀት / MARRIAGE CERTIFICATE';
        case 'divorce':
            return 'የፍቺ ምስክር ወረቀት / DIVORCE CERTIFICATE';
        case 'death':
            return 'የሞት ምስክር ወረቀት / DEATH CERTIFICATE';
        default:
            return strtoupper($type) . ' CERTIFICATE';
    }
}

function getDescriptionLine($type)
{
    switch ($type) {
        case 'birth':
            return 'ይህ የሚመሰክረው ከዚህ በታች የተመዘገበው ሕጻን በሕጋዊ መንገድ ተመዝግቧል።';
        case 'marriage':
            return 'ይህ የሚመሰክረው ከዚህ በታች የተመዘገቡት ጥንዶች በሕጋዊ መንገድ ተመዝግበዋል።';
        case 'divorce':
            return 'ይህ የሚመሰክረው ከዚህ በታች የተመዘገቡት አካላት ፍቺያቸው በሕጋዊ መንገድ ተመዝግቧል።';
        case 'death':
            return 'ይህ የሚመሰክረው ከዚህ በታች የተመዘገበው ሰው መሞቱ በሕጋዊ መንገድ ተመዝግቧል።';
        default:
            return 'This is to certify that the following has been duly registered.';
    }
}

$photo_data = getPhoto($type, $data);
$signature = getRegistrarSignature($data);
?>

<div class="certificate-preview" style="max-width: 900px; margin: 0 auto; font-family: 'Times New Roman', Times, serif;">
    <div style="border: 15px double #005dac; padding: 25px; background: white; position: relative;">

        <div style="position: absolute; top: 5px; left: 5px; right: 5px; bottom: 5px; border: 1px solid #005dac; opacity: 0.2;"></div>

        <!-- Header -->
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="display: flex; justify-content: center; align-items: center; gap: 20px; margin-bottom: 10px;">
                <div style="width: 55px; height: 55px; border: 2px solid #005dac; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 28px;">🇪🇹</span>
                </div>
                <div>
                    <h2 style="color: #005dac; margin: 0; font-size: 18px;">የኢትዮጵያ ፌዴራላዊ ዴሞክራሲያዊ ሪፐብሊክ</h2>
                    <h3 style="color: #333; margin: 3px 0; font-size: 14px;">FEDERAL DEMOCRATIC REPUBLIC OF ETHIOPIA</h3>
                    <p style="color: #666; font-size: 11px;">የወሳኝ ኩነት ምዝገባ ኤጀንሲ - Vital Events Registration Agency</p>
                </div>
                <div style="width: 55px; height: 55px; border: 2px solid #005dac; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 28px;">🇪🇹</span>
                </div>
            </div>
            <div style="border-top: 2px solid #005dac; border-bottom: 2px solid #005dac; padding: 8px; margin: 15px 0;">
                <h1 style="color: #005dac; margin: 0; font-size: 20px; text-transform: uppercase;"><?php echo getCertificateTitle($type); ?></h1>
            </div>
        </div>

        <!-- Certificate Numbers -->
        <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 11px; background: #f9f9f9; padding: 8px;">
            <div><strong>ምዝገባ ቁጥር / Reg. No:</strong> <?php echo htmlspecialchars($data['registration_number'] ?? 'N/A'); ?></div>
            <div><strong>የምስክር ወረቀት ቁጥር / Cert. No:</strong> <?php echo htmlspecialchars($data['certificate_number'] ?? 'N/A'); ?></div>
            <div><strong>ቀን / Date:</strong> <?php echo getEthiopianDate($data['approved_at']); ?></div>
        </div>

        <!-- Photo Section -->
        <?php if ($type == 'marriage' || $type == 'divorce'):
            $husband_photo = is_array($photo_data) ? ($photo_data['husband'] ?? '') : '';
            $wife_photo = is_array($photo_data) ? ($photo_data['wife'] ?? '') : '';
            $husband_photo_url = getPhotoPath($husband_photo);
            $wife_photo_url = getPhotoPath($wife_photo);
        ?>
            <div style="display: flex; justify-content: space-between; gap: 10px; margin-bottom: 15px;">
                <div style="flex: 1; border: 1px solid #ccc; padding: 5px; background: white; text-align: center;">
                    <?php if ($husband_photo_url): ?>
                        <img src="<?php echo $husband_photo_url; ?>" style="width: 100%; height: 130px; object-fit: cover;" alt="Husband Photo">
                    <?php else: ?>
                        <div style="width: 100%; height: 130px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999; font-size: 11px;">
                            የባል ፎቶ<br>Husband Photo
                        </div>
                    <?php endif; ?>
                    <p style="font-size: 8px; margin: 2px 0;">የባል ፎቶ / Husband Photo (3x4)</p>
                </div>
                <div style="flex: 1; border: 1px solid #ccc; padding: 5px; background: white; text-align: center;">
                    <?php if ($wife_photo_url): ?>
                        <img src="<?php echo $wife_photo_url; ?>" style="width: 100%; height: 130px; object-fit: cover;" alt="Wife Photo">
                    <?php else: ?>
                        <div style="width: 100%; height: 130px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999; font-size: 11px;">
                            የሚስት ፎቶ<br>Wife Photo
                        </div>
                    <?php endif; ?>
                    <p style="font-size: 8px; margin: 2px 0;">የሚስት ፎቶ / Wife Photo (3x4)</p>
                </div>
            </div>
        <?php else:
            $photo_path = is_string($photo_data) ? $photo_data : '';
            $photo_url = getPhotoPath($photo_path);
        ?>
            <div style="float: right; width: 110px; margin-left: 15px; margin-bottom: 10px; border: 1px solid #ccc; padding: 3px; background: white; text-align: center;">
                <?php if ($photo_url): ?>
                    <img src="<?php echo $photo_url; ?>" style="width: 100%; height: 130px; object-fit: cover;" alt="Photo">
                <?php else: ?>
                    <div style="width: 100%; height: 130px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999; font-size: 11px;">
                        <?php echo ($type == 'birth') ? 'የሕጻኑ ፎቶ' : (($type == 'death') ? 'የሟቹ ፎቶ' : 'ፎቶ'); ?><br>Photo
                    </div>
                <?php endif; ?>
                <p style="font-size: 8px; margin: 2px 0;">3x4 ሴ.ሜ / 3x4 cm</p>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div style="<?php echo ($type == 'marriage' || $type == 'divorce') ? '' : 'margin-right: 130px;'; ?>">
            <div style="margin-bottom: 15px;">
                <p style="margin: 0; font-size: 12px; line-height: 1.4;"><?php echo getDescriptionLine($type); ?></p>
                <p style="margin: 3px 0 0; font-size: 11px; font-style: italic; color: #555;">This is to certify that</p>
                <h3 style="color: #005dac; margin: 10px 0 5px; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
                    <?php echo htmlspecialchars(getFullName($type, $data)); ?>
                </h3>
            </div>

            <!-- Title for Death Certificate -->
            <?php if ($type == 'death' && getTitle($data) != 'N/A'): ?>
                <div style="margin-bottom: 10px; padding: 5px; background: #f9f9f9;">
                    <strong>ማዕረግ / Title:</strong> <?php echo htmlspecialchars(getTitle($data)); ?>
                </div>
            <?php endif; ?>

            <!-- Personal Details Table -->
            <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 10px;">
                <tr>
                    <td style="padding: 5px; width: 35%; background: #f5f5f5;"><strong>የአባት ስም / Father's Name:</strong></td>
                    <td colspan="3"><?php echo htmlspecialchars(getFatherName($type, $data)); ?></td>
                </tr>
                <tr>
                    <td style="padding: 5px; background: #f5f5f5;"><strong>የአያት ስም / Grandfather's Name:</strong></td>
                    <td colspan="3"><?php echo htmlspecialchars(getGrandFatherName($type, $data)); ?></td>
                </tr>
                <tr>
                    <td style="padding: 5px; background: #f5f5f5;"><strong>የእናት ስም / Mother's Name:</strong></td>
                    <td colspan="3"><?php echo htmlspecialchars(getMotherName($type, $data)); ?></td>
                </tr>

                <!-- Spouse Name (for marriage/divorce) -->
                <?php if ($type == 'marriage' || $type == 'divorce'): ?>
                    <tr>
                        <td style="padding: 5px; background: #f5f5f5;"><strong><?php echo ($type == 'marriage') ? 'የሙሽሪቱ ስም / Bride Name:' : 'የሚስቱ ስም / Wife Name:'; ?></strong></td>
                        <td colspan="3"><?php echo htmlspecialchars(getSpouseName($type, $data)); ?></td>
                    </tr>
                <?php endif; ?>

                <!-- Nationalities -->
                <?php if ($type == 'marriage' || $type == 'divorce'): ?>
                    <tr>
                        <td style="padding: 5px; background: #f5f5f5;"><strong>የባል ዜግነት / Husband Nationality:</strong></td>
                        <td style="padding: 5px;"><?php echo htmlspecialchars(getNationality($type, $data)); ?></td>
                        <td style="padding: 5px; background: #f5f5f5;"><strong>የሚስት ዜግነት / Wife Nationality:</strong></td>
                        <td style="padding: 5px;"><?php echo htmlspecialchars(getSpouseNationality($type, $data)); ?></td>
                    </tr>
                <?php elseif ($type == 'birth' || $type == 'death'): ?>
                    <tr>
                        <td style="padding: 5px; background: #f5f5f5;"><strong>ጾታ / Sex:</strong></td>
                        <td style="padding: 5px;"><?php echo htmlspecialchars(getGender($type, $data)); ?></td>
                        <td style="padding: 5px; background: #f5f5f5;"><strong>ዜግነት / Nationality:</strong></td>
                        <td style="padding: 5px;"><?php echo htmlspecialchars(getNationality($type, $data)); ?></td>
                    </tr>
                <?php endif; ?>

                <!-- Date of Event -->
                <tr>
                    <td style="padding: 5px; background: #f5f5f5;"><strong><?php echo ($type == 'birth') ? 'የትውልድ ቀን / Date of Birth:' : (($type == 'death') ? 'የሞት ቀን / Date of Death:' : 'የጋብቻ/ፍቺ ቀን / Event Date:'); ?></strong></td>
                    <td style="padding: 5px;"><?php echo getEventDate($type, $data); ?></td>
                    <td style="padding: 5px; background: #f5f5f5;"><strong>የተከሰተበት ቦታ / Place of Event:</strong></td>
                    <td style="padding: 5px;"><?php echo htmlspecialchars(getEventPlace($type, $data)); ?></td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        <!-- Registration Details -->
        <div style="margin-top: 15px; background: #e8f0fe; padding: 10px; font-size: 11px;">
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                <div><strong>📅 የተመዘገበበት ቀን / Registration Date:</strong> <?php echo getRegistrationDate($data); ?></div>
                <div><strong>✅ የጸደቀበት ቀን / Approved Date:</strong> <?php echo getApprovedDate($data); ?></div>
                <div><strong>👨‍⚖️ አጽድቋል / Approved By:</strong> <?php echo htmlspecialchars(getDirectorGeneralName($data)); ?></div>
            </div>
        </div>

        <!-- Signatures with saved admin signature -->
        <div style="display: flex; justify-content: space-between; margin-top: 25px;">
            <div style="text-align: center; width: 30%;">
                <?php if ($signature && strpos($signature, 'data:image') === 0): ?>
                    <img src="<?php echo $signature; ?>" style="height: 50px; max-width: 150px; margin-bottom: 5px;">
                <?php else: ?>
                    <div style="border-top: 1px solid #000; width: 100%; margin: 10px 0;"></div>
                <?php endif; ?>
                <p style="font-size: 10px; margin: 0;">የክብር መዝገብ ሹም ፊርማ</p>
                <p style="font-size: 9px; margin: 0;">Registrar Signature</p>
                <p style="font-size: 9px; color: #666;"><?php echo htmlspecialchars(getDirectorGeneralName($data)); ?></p>
            </div>
            <div style="text-align: center; width: 30%;">
                <div style="border-top: 1px solid #000; width: 100%; margin: 10px 0;"></div>
                <p style="font-size: 10px; margin: 0;">የዋና ዳይሬክተር ፊርማ</p>
                <p style="font-size: 9px; margin: 0;">Director General Signature</p>
                <p style="font-size: 9px; color: #666;"><?php echo htmlspecialchars(getDirectorGeneralName($data)); ?></p>
            </div>
            <div style="text-align: center; width: 30%;">
                <div style="width: 60px; height: 60px; margin: 0 auto; border: 1px solid #005dac; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 24px;">🇪🇹</span>
                </div>
                <p style="font-size: 9px; margin: 5px 0;">የኤጀንሲ ማህተም</p>
                <p style="font-size: 9px; margin: 0;">Agency Seal</p>
            </div>
        </div>

        <!-- Footer -->
        <div style="text-align: center; margin-top: 20px; font-size: 9px; color: #666; border-top: 1px solid #eee; padding-top: 10px;">
            <p>ይህ ሰርተፍኬት በኢትዮጵያ ፌዴራላዊ ዴሞክራሲያዊ ሪፐብሊክ ህግ መሰረት የተሰጠ ነው</p>
            <p>This certificate is issued under the laws of the Federal Democratic Republic of Ethiopia</p>
            <p style="font-size: 8px; margin-top: 5px;">QR Code: VE-<?php echo $data['certificate_number'] . '-' . $data['id']; ?></p>
        </div>
    </div>
</div>

<style>
    @media print {
        .certificate-preview {
            margin: 0;
            padding: 0;
        }

        .certificate-preview>div {
            box-shadow: none;
            border: 1px solid #ccc;
        }
    }
</style>
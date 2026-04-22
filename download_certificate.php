<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login_simple.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';
$format = isset($_GET['format']) ? $_GET['format'] : 'jpg';

$table = $type . '_registrations';
$sql = "SELECT * FROM $table WHERE id = ? AND user_id = ? AND status = 'approved'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die('Certificate not found or not approved');
}

// ============================================
// HELPER FUNCTIONS (same as preview_certificate.php)
// ============================================

function getFullNameCert($type, $data)
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

function getFatherNameCert($type, $data)
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

function getGrandFatherNameCert($type, $data)
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

function getMotherNameCert($type, $data)
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

function getSpouseNameCert($type, $data)
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

function getGenderCert($type, $data)
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

function getTitleCert($data)
{
    return $data['title'] ?? 'N/A';
}

function getEventDateCert($type, $data)
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

function getEventPlaceCert($type, $data)
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

function getNationalityCert($type, $data)
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

function getSpouseNationalityCert($type, $data)
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

function getRegistrationDateCert($data)
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

function getApprovedDateCert($data)
{
    return date('F d, Y', strtotime($data['approved_at']));
}

function getDirectorGeneralNameCert($data)
{
    if (isset($data['registrar_full_name']) && !empty($data['registrar_full_name'])) {
        return $data['registrar_full_name'];
    }
    if (isset($data['registrar_name']) && !empty($data['registrar_name'])) {
        return $data['registrar_name'];
    }
    return '_________________________';
}

function getRegistrarSignatureCert($data)
{
    if (isset($data['registrar_signature']) && !empty($data['registrar_signature'])) {
        return $data['registrar_signature'];
    }
    return '';
}

function getPhotoCert($type, $data)
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

function getPhotoPathCert($photo)
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

function getEthiopianDateCert($date)
{
    return date('d/m/Y', strtotime($date));
}

function getCertificateTitleCert($type)
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

function getDescriptionLineCert($type)
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

$photo_data = getPhotoCert($type, $data);
$signature = getRegistrarSignatureCert($data);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Certificate of <?php echo ucfirst($type); ?></title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            background: white;
            margin: 0;
            padding: 0;
        }

        .certificate {
            border: 15px double #005dac;
            padding: 25px;
            background: white;
            position: relative;
            max-width: 900px;
            margin: 0 auto;
        }

        .certificate .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.03;
            font-size: 70px;
            font-weight: bold;
            white-space: nowrap;
            text-align: center;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header-logo {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 10px;
        }

        .header-logo .emblem {
            width: 55px;
            height: 55px;
            border: 2px solid #005dac;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-logo .emblem span {
            font-size: 28px;
        }

        .header h2 {
            color: #005dac;
            margin: 0;
            font-size: 18px;
            letter-spacing: 1px;
        }

        .header h3 {
            color: #333;
            margin: 3px 0;
            font-size: 14px;
            font-weight: normal;
        }

        .header p {
            color: #666;
            font-size: 11px;
            margin: 0;
        }

        .certificate-title {
            border-top: 2px solid #005dac;
            border-bottom: 2px solid #005dac;
            padding: 8px;
            margin: 15px 0;
        }

        .certificate-title h1 {
            color: #005dac;
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .certificate-no {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 11px;
            background: #f9f9f9;
            padding: 8px;
        }

        /* Photo Styles */
        .dual-photos {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 15px;
        }

        .photo-box {
            flex: 1;
            border: 1px solid #ccc;
            padding: 5px;
            background: white;
            text-align: center;
        }

        .photo-box img {
            width: 100%;
            height: 130px;
            object-fit: cover;
        }

        .photo-placeholder {
            width: 100%;
            height: 130px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 11px;
        }

        .single-photo {
            float: right;
            width: 110px;
            margin-left: 15px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            padding: 3px;
            background: white;
            text-align: center;
        }

        .single-photo img {
            width: 100%;
            height: 130px;
            object-fit: cover;
        }

        .photo-caption {
            font-size: 8px;
            margin: 2px 0;
        }

        .main-content {
            margin-right: 130px;
        }


        .description {
            margin-bottom: 15px;
        }

        .description p {
            margin: 0;
            font-size: 12px;
            line-height: 1.4;
        }

        .description .english-desc {
            margin: 3px 0 0;
            font-size: 11px;
            font-style: italic;
            color: #555;
        }

        .name-title {
            color: #005dac;
            margin: 10px 0 5px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            font-size: 16px;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 10px;
        }

        .info-table td {
            padding: 5px;
        }

        .info-table .label {
            background: #f5f5f5;
            width: 35%;
        }

        .title-box {
            margin-bottom: 10px;
            padding: 5px;
            background: #f9f9f9;
        }

        .registration-details {
            margin-top: 15px;
            background: #e8f0fe;
            padding: 10px;
            font-size: 11px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            clear: both;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
        }

        .signature-box {
            text-align: center;
            width: 30%;
        }

        .signature-img {
            height: 50px;
            max-width: 150px;
            margin-bottom: 5px;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 100%;
            margin: 10px 0;
        }

        .seal-circle {
            width: 60px;
            height: 60px;
            margin: 0 auto;
            border: 1px solid #005dac;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .seal-circle span {
            font-size: 24px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .clearfix {
            clear: both;
        }

        @media print {
            .certificate {
                box-shadow: none;
                border: 1px solid #ccc;
            }
        }
    </style>
</head>

<body>
    <div class="certificate">
        <div class="watermark">VITAL EVENTS<br>ETHIOPIA</div>

        <!-- Header -->
        <div class="header">
            <div class="header-logo">
                <div class="emblem"><span>🇪🇹</span></div>
                <div>
                    <h2>የኢትዮጵያ ፌዴራላዊ ዴሞክራሲያዊ ሪፐብሊክ</h2>
                    <h3>FEDERAL DEMOCRATIC REPUBLIC OF ETHIOPIA</h3>
                    <p>የወሳኝ ኩነት ምዝገባ ኤጀንሲ - Vital Events Registration Agency</p>
                </div>
                <div class="emblem"><span>🇪🇹</span></div>
            </div>
            <div class="certificate-title">
                <h1><?php echo getCertificateTitleCert($type); ?></h1>
            </div>
        </div>

        <!-- Certificate Numbers -->
        <div class="certificate-no">
            <div><strong>ምዝገባ ቁጥር / Reg. No:</strong> <?php echo htmlspecialchars($data['registration_number'] ?? 'N/A'); ?></div>
            <div><strong>የምስክር ወረቀት ቁጥር / Cert. No:</strong> <?php echo htmlspecialchars($data['certificate_number'] ?? 'N/A'); ?></div>
            <div><strong>ቀን / Date:</strong> <?php echo getEthiopianDateCert($data['approved_at']); ?></div>
        </div>

        <!-- Photo Section -->
        <?php if ($type == 'marriage' || $type == 'divorce'):
            $husband_photo = is_array($photo_data) ? ($photo_data['husband'] ?? '') : '';
            $wife_photo = is_array($photo_data) ? ($photo_data['wife'] ?? '') : '';
            $husband_photo_url = getPhotoPathCert($husband_photo);
            $wife_photo_url = getPhotoPathCert($wife_photo);
        ?>
            <div class="dual-photos">
                <div class="photo-box">
                    <?php if ($husband_photo_url): ?>
                        <img src="<?php echo $husband_photo_url; ?>" alt="Husband Photo">
                    <?php else: ?>
                        <div class="photo-placeholder">የባል ፎቶ<br>Husband Photo</div>
                    <?php endif; ?>
                    <div class="photo-caption">የባል ፎቶ / Husband Photo (3x4)</div>
                </div>
                <div class="photo-box">
                    <?php if ($wife_photo_url): ?>
                        <img src="<?php echo $wife_photo_url; ?>" alt="Wife Photo">
                    <?php else: ?>
                        <div class="photo-placeholder">የሚስት ፎቶ<br>Wife Photo</div>
                    <?php endif; ?>
                    <div class="photo-caption">የሚስት ፎቶ / Wife Photo (3x4)</div>
                </div>
            </div>
        <?php else:
            $photo_path = is_string($photo_data) ? $photo_data : '';
            $photo_url = getPhotoPathCert($photo_path);
        ?>
            <div class="single-photo">
                <?php if ($photo_url): ?>
                    <img src="<?php echo $photo_url; ?>" alt="Photo">
                <?php else: ?>
                    <div class="photo-placeholder" style="height:130px;">
                        <?php echo ($type == 'birth') ? 'የሕጻኑ ፎቶ' : (($type == 'death') ? 'የሟቹ ፎቶ' : 'ፎቶ'); ?><br>Photo
                    </div>
                <?php endif; ?>
                <div class="photo-caption">3x4 ሴ.ሜ / 3x4 cm</div>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="<?php echo ($type == 'marriage' || $type == 'divorce') ? 'main-content-no-float' : 'main-content'; ?>">
            <div class="description">
                <p><?php echo getDescriptionLineCert($type); ?></p>
                <p class="english-desc">This is to certify that</p>
                <h3 class="name-title"><?php echo htmlspecialchars(getFullNameCert($type, $data)); ?></h3>
            </div>

            <!-- Title for Death Certificate -->
            <?php if ($type == 'death' && getTitleCert($data) != 'N/A'): ?>
                <div class="title-box">
                    <strong>ማዕረግ / Title:</strong> <?php echo htmlspecialchars(getTitleCert($data)); ?>
                </div>
            <?php endif; ?>

            <!-- Personal Details Table -->
            <table class="info-table">
                <tr>
                    <td class="label"><strong>የአባት ስም / Father's Name:</strong></td>
                    <td colspan="3"><?php echo htmlspecialchars(getFatherNameCert($type, $data)); ?></td>
                </tr>
                <tr>
                    <td class="label"><strong>የአያት ስም / Grandfather's Name:</strong></td>
                    <td colspan="3"><?php echo htmlspecialchars(getGrandFatherNameCert($type, $data)); ?></td>
                </tr>
                <tr>
                    <td class="label"><strong>የእናት ስም / Mother's Name:</strong></td>
                    <td colspan="3"><?php echo htmlspecialchars(getMotherNameCert($type, $data)); ?></td>
                </tr>

                <!-- Spouse Name (for marriage/divorce) -->
                <?php if ($type == 'marriage' || $type == 'divorce'): ?>
                    <tr>
                        <td class="label"><strong><?php echo ($type == 'marriage') ? 'የሙሽሪቱ ስም / Bride Name:' : 'የሚስቱ ስም / Wife Name:'; ?></strong></td>
                        <td colspan="3"><?php echo htmlspecialchars(getSpouseNameCert($type, $data)); ?></td>
                    </tr>
                <?php endif; ?>

                <!-- Nationalities -->
                <?php if ($type == 'marriage' || $type == 'divorce'): ?>
                    <tr>
                        <td class="label"><strong>የባል ዜግነት / Husband Nationality:</strong></td>
                        <td><?php echo htmlspecialchars(getNationalityCert($type, $data)); ?></td>
                        <td class="label"><strong>የሚስት ዜግነት / Wife Nationality:</strong></td>
                        <td><?php echo htmlspecialchars(getSpouseNationalityCert($type, $data)); ?></td>
                    </tr>
                <?php elseif ($type == 'birth' || $type == 'death'): ?>
                    <tr>
                        <td class="label"><strong>ጾታ / Sex:</strong></td>
                        <td><?php echo htmlspecialchars(getGenderCert($type, $data)); ?></td>
                        <td class="label"><strong>ዜግነት / Nationality:</strong></td>
                        <td><?php echo htmlspecialchars(getNationalityCert($type, $data)); ?></td>
                    </tr>
                <?php endif; ?>

                <!-- Date of Event -->
                <tr>
                    <td class="label"><strong><?php echo ($type == 'birth') ? 'የትውልድ ቀን / Date of Birth:' : (($type == 'death') ? 'የሞት ቀን / Date of Death:' : 'የጋብቻ/ፍቺ ቀን / Event Date:'); ?></strong></td>
                    <td><?php echo getEventDateCert($type, $data); ?></td>
                    <td class="label"><strong>የተከሰተበት ቦታ / Place of Event:</strong></td>
                    <td><?php echo htmlspecialchars(getEventPlaceCert($type, $data)); ?></td>
                </tr>
            </table>
        </div>

        <div class="clearfix"></div>

        <!-- Registration Details -->
        <div class="registration-details">
            <div><strong>📅 የተመዘገበበት ቀን / Registration Date:</strong> <?php echo getRegistrationDateCert($data); ?></div>
            <div><strong>✅ የጸደቀበት ቀን / Approved Date:</strong> <?php echo getApprovedDateCert($data); ?></div>
            <div><strong>👨‍⚖️ አጽድቋል / Approved By:</strong> <?php echo htmlspecialchars(getDirectorGeneralNameCert($data)); ?></div>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <?php if ($signature && strpos($signature, 'data:image') === 0): ?>
                    <img src="<?php echo $signature; ?>" class="signature-img">
                <?php else: ?>
                    <div class="signature-line"></div>
                <?php endif; ?>
                <p style="font-size: 10px; margin: 0;">የክብር መዝገብ ሹም ፊርማ</p>
                <p style="font-size: 9px; margin: 0;">Registrar Signature</p>
                <p style="font-size: 9px; color: #666;"><?php echo htmlspecialchars(getDirectorGeneralNameCert($data)); ?></p>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <p style="font-size: 10px; margin: 0;">የዋና ዳይሬክተር ፊርማ</p>
                <p style="font-size: 9px; margin: 0;">Director General Signature</p>
                <p style="font-size: 9px; color: #666;"><?php echo htmlspecialchars(getDirectorGeneralNameCert($data)); ?></p>
            </div>
            <div class="signature-box">
                <div class="seal-circle"><span>🇪🇹</span></div>
                <p style="font-size: 9px; margin: 5px 0;">የኤጀንሲ ማህተም</p>
                <p style="font-size: 9px; margin: 0;">Agency Seal</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>ይህ ሰርተፍኬት በኢትዮጵያ ፌዴራላዊ ዴሞክራሲያዊ ሪፐብሊክ ህግ መሰረት የተሰጠ ነው</p>
            <p>This certificate is issued under the laws of the Federal Democratic Republic of Ethiopia</p>
            <p style="font-size: 8px; margin-top: 5px;">QR Code: VE-<?php echo $data['certificate_number'] . '-' . $data['id']; ?></p>
        </div>
    </div>

    <?php if ($format == 'pdf'): ?>
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    <?php endif; ?>
</body>

</html>
<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
/** @var mysqli $conn */
global $conn;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    
    // ============================================
    // HANDLE COUPLE PHOTO UPLOAD (3x4)
    // ============================================
    $couple_photo_path = null;
    if (isset($_FILES['couple_photo']) && $_FILES['couple_photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['couple_photo']['name'];
        $fileext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($fileext, $allowed)) {
            // Create uploads directory if not exists
            $upload_dir = '../assets/uploads/marriage_photos/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $new_filename = 'marriage_' . $user_id . '_' . time() . '.' . $fileext;
            $upload_path = $upload_dir . $new_filename;
            
            // Resize image to 3x4 proportions (300x400 pixels for 3x4 cm)
            $src = null;
            if ($fileext == 'jpg' || $fileext == 'jpeg') {
                $src = imagecreatefromjpeg($_FILES['couple_photo']['tmp_name']);
            } elseif ($fileext == 'png') {
                $src = imagecreatefrompng($_FILES['couple_photo']['tmp_name']);
            }
            
            if ($src) {
                $orig_width = imagesx($src);
                $orig_height = imagesy($src);
                $target_width = 300;
                $target_height = 400;
                
                $dst = imagecreatetruecolor($target_width, $target_height);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $target_width, $target_height, $orig_width, $orig_height);
                
                if ($fileext == 'jpg' || $fileext == 'jpeg') {
                    imagejpeg($dst, $upload_path, 90);
                } else {
                    imagepng($dst, $upload_path, 9);
                }
                
                imagedestroy($src);
                imagedestroy($dst);
                
                $couple_photo_path = 'assets/uploads/marriage_photos/' . $new_filename;
            } else {
                move_uploaded_file($_FILES['couple_photo']['tmp_name'], $upload_path);
                $couple_photo_path = 'assets/uploads/marriage_photos/' . $new_filename;
            }
        }
    }
    
    // Get form data
    $marriage_form_number = isset($_POST['marriage_form_number']) ? sanitize($_POST['marriage_form_number']) : '';
    $marriage_unique_id = isset($_POST['marriage_unique_id']) ? sanitize($_POST['marriage_unique_id']) : '';
    
    // Wife (Bride) Information
    $wife_name = isset($_POST['wife_name']) ? sanitize($_POST['wife_name']) : '';
    $wife_father_name = isset($_POST['wife_father_name']) ? sanitize($_POST['wife_father_name']) : '';
    $wife_grandfather_name = isset($_POST['wife_grandfather_name']) ? sanitize($_POST['wife_grandfather_name']) : '';
    $wife_birth_month = isset($_POST['wife_birth_month']) ? sanitize($_POST['wife_birth_month']) : '';
    $wife_birth_day = isset($_POST['wife_birth_day']) ? (int)$_POST['wife_birth_day'] : 0;
    $wife_birth_year = isset($_POST['wife_birth_year']) ? (int)$_POST['wife_birth_year'] : 0;
    $wife_nationality = isset($_POST['wife_nationality']) ? sanitize($_POST['wife_nationality']) : 'Ethiopian';
    
    // Husband (Groom) Information
    $husband_name = isset($_POST['husband_name']) ? sanitize($_POST['husband_name']) : '';
    $husband_father_name = isset($_POST['husband_father_name']) ? sanitize($_POST['husband_father_name']) : '';
    $husband_grandfather_name = isset($_POST['husband_grandfather_name']) ? sanitize($_POST['husband_grandfather_name']) : '';
    $husband_birth_month = isset($_POST['husband_birth_month']) ? sanitize($_POST['husband_birth_month']) : '';
    $husband_birth_day = isset($_POST['husband_birth_day']) ? (int)$_POST['husband_birth_day'] : 0;
    $husband_birth_year = isset($_POST['husband_birth_year']) ? (int)$_POST['husband_birth_year'] : 0;
    $husband_nationality = isset($_POST['husband_nationality']) ? sanitize($_POST['husband_nationality']) : 'Ethiopian';
    
    // Marriage Information
    $marriage_month = isset($_POST['marriage_month']) ? sanitize($_POST['marriage_month']) : '';
    $marriage_day = isset($_POST['marriage_day']) ? (int)$_POST['marriage_day'] : 0;
    $marriage_year = isset($_POST['marriage_year']) ? (int)$_POST['marriage_year'] : 0;
    
    // Registration Place
    $region = isset($_POST['region']) ? sanitize($_POST['region']) : '';
    $zone = isset($_POST['zone']) ? sanitize($_POST['zone']) : '';
    $city = isset($_POST['city']) ? sanitize($_POST['city']) : '';
    $sub_city = isset($_POST['sub_city']) ? sanitize($_POST['sub_city']) : '';
    $woreda = isset($_POST['woreda']) ? sanitize($_POST['woreda']) : '';
    $kebele = isset($_POST['kebele']) ? sanitize($_POST['kebele']) : '';
    
    // Dates
    $registration_date = isset($_POST['registration_date']) ? sanitize($_POST['registration_date']) : null;
    $certificate_issue_date = isset($_POST['certificate_issue_date']) ? sanitize($_POST['certificate_issue_date']) : null;
    
    // Registrar Information
    $registrar_name = isset($_POST['registrar_name']) ? sanitize($_POST['registrar_name']) : '';
    $registrar_father_name = isset($_POST['registrar_father_name']) ? sanitize($_POST['registrar_father_name']) : '';
    $registrar_grandfather_name = isset($_POST['registrar_grandfather_name']) ? sanitize($_POST['registrar_grandfather_name']) : '';
    
    // Create marriage date
    $month_num = 0;
    $months = ['January'=>1,'February'=>2,'March'=>3,'April'=>4,'May'=>5,'June'=>6,'July'=>7,'August'=>8,'September'=>9,'October'=>10,'November'=>11,'December'=>12];
    if (isset($months[$marriage_month])) {
        $month_num = $months[$marriage_month];
    }
    $marriage_date = $marriage_year . '-' . sprintf('%02d', $month_num) . '-' . sprintf('%02d', $marriage_day);
    
    $registration_number = generateRegistrationNumber('MAR');
    
    // Check what columns exist in the table
    $check_columns = mysqli_query($conn, "SHOW COLUMNS FROM marriage_registrations");
    $existing_columns = [];
    while ($col = mysqli_fetch_assoc($check_columns)) {
        $existing_columns[] = $col['Field'];
    }
    
    // Build insert query based on existing columns
    $columns = ['user_id', 'groom_name', 'bride_name', 'marriage_date', 'registration_number', 'status'];
    $values = [$user_id, $husband_name, $wife_name, $marriage_date, $registration_number, 'pending'];
    
    // Add couple photo if uploaded
    if ($couple_photo_path && in_array('couple_photo', $existing_columns)) {
        $columns[] = 'couple_photo';
        $values[] = $couple_photo_path;
    }
    
    // Add optional columns if they exist
    if (in_array('marriage_form_number', $existing_columns) && !empty($marriage_form_number)) {
        $columns[] = 'marriage_form_number';
        $values[] = $marriage_form_number;
    }
    if (in_array('marriage_unique_id', $existing_columns) && !empty($marriage_unique_id)) {
        $columns[] = 'marriage_unique_id';
        $values[] = $marriage_unique_id;
    }
    if (in_array('wife_father_name', $existing_columns) && !empty($wife_father_name)) {
        $columns[] = 'wife_father_name';
        $values[] = $wife_father_name;
    }
    if (in_array('wife_grandfather_name', $existing_columns) && !empty($wife_grandfather_name)) {
        $columns[] = 'wife_grandfather_name';
        $values[] = $wife_grandfather_name;
    }
    if (in_array('husband_father_name', $existing_columns) && !empty($husband_father_name)) {
        $columns[] = 'husband_father_name';
        $values[] = $husband_father_name;
    }
    if (in_array('husband_grandfather_name', $existing_columns) && !empty($husband_grandfather_name)) {
        $columns[] = 'husband_grandfather_name';
        $values[] = $husband_grandfather_name;
    }
    if (in_array('wife_nationality', $existing_columns) && !empty($wife_nationality)) {
        $columns[] = 'wife_nationality';
        $values[] = $wife_nationality;
    }
    if (in_array('husband_nationality', $existing_columns) && !empty($husband_nationality)) {
        $columns[] = 'husband_nationality';
        $values[] = $husband_nationality;
    }
    if (in_array('marriage_place', $existing_columns) && !empty($city)) {
        $columns[] = 'marriage_place';
        $values[] = $city;
    }
    if (in_array('witnesses', $existing_columns)) {
        $columns[] = 'witnesses';
        $values[] = '';
    }
    if (in_array('region', $existing_columns) && !empty($region)) {
        $columns[] = 'region';
        $values[] = $region;
    }
    if (in_array('zone', $existing_columns) && !empty($zone)) {
        $columns[] = 'zone';
        $values[] = $zone;
    }
    if (in_array('sub_city', $existing_columns) && !empty($sub_city)) {
        $columns[] = 'sub_city';
        $values[] = $sub_city;
    }
    if (in_array('woreda', $existing_columns) && !empty($woreda)) {
        $columns[] = 'woreda';
        $values[] = $woreda;
    }
    if (in_array('kebele', $existing_columns) && !empty($kebele)) {
        $columns[] = 'kebele';
        $values[] = $kebele;
    }
    if (in_array('registration_date', $existing_columns) && !empty($registration_date)) {
        $columns[] = 'registration_date';
        $values[] = $registration_date;
    }
    if (in_array('certificate_issue_date', $existing_columns) && !empty($certificate_issue_date)) {
        $columns[] = 'certificate_issue_date';
        $values[] = $certificate_issue_date;
    }
    if (in_array('registrar_name', $existing_columns) && !empty($registrar_name)) {
        $columns[] = 'registrar_name';
        $values[] = $registrar_name;
    }
    if (in_array('registrar_father_name', $existing_columns) && !empty($registrar_father_name)) {
        $columns[] = 'registrar_father_name';
        $values[] = $registrar_father_name;
    }
    if (in_array('registrar_grandfather_name', $existing_columns) && !empty($registrar_grandfather_name)) {
        $columns[] = 'registrar_grandfather_name';
        $values[] = $registrar_grandfather_name;
    }
    
    // Build the query
    $placeholders = implode(',', array_fill(0, count($values), '?'));
    $sql = "INSERT INTO marriage_registrations (" . implode(',', $columns) . ") VALUES (" . $placeholders . ")";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    // Create types string (first is integer, rest are strings)
    $types = 'i' . str_repeat('s', count($values) - 1);
    
    // Bind parameters dynamically
    $bind_params = [$types];
    for ($i = 0; $i < count($values); $i++) {
        $bind_params[] = &$values[$i];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Marriage registration submitted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
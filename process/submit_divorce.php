<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

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
    // HANDLE PARTIES PHOTO UPLOAD (3x4)
    // ============================================
    $parties_photo_path = null;
    if (isset($_FILES['parties_photo']) && $_FILES['parties_photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['parties_photo']['name'];
        $fileext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($fileext, $allowed)) {
            // Create uploads directory if not exists
            $upload_dir = '../assets/uploads/divorce_photos/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $new_filename = 'divorce_' . $user_id . '_' . time() . '.' . $fileext;
            $upload_path = $upload_dir . $new_filename;
            
            // Resize image to 3x4 proportions (300x400 pixels for 3x4 cm)
            $src = null;
            if ($fileext == 'jpg' || $fileext == 'jpeg') {
                $src = imagecreatefromjpeg($_FILES['parties_photo']['tmp_name']);
            } elseif ($fileext == 'png') {
                $src = imagecreatefrompng($_FILES['parties_photo']['tmp_name']);
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
                
                $parties_photo_path = 'assets/uploads/divorce_photos/' . $new_filename;
            } else {
                move_uploaded_file($_FILES['parties_photo']['tmp_name'], $upload_path);
                $parties_photo_path = 'assets/uploads/divorce_photos/' . $new_filename;
            }
        }
    }
    
    // Get form data
    $divorce_form_number = isset($_POST['divorce_form_number']) ? sanitize($_POST['divorce_form_number']) : '';
    $divorce_unique_id = isset($_POST['divorce_unique_id']) ? sanitize($_POST['divorce_unique_id']) : '';
    
    // Divorcée (Wife) Information
    $divorcee_name = isset($_POST['divorcee_name']) ? sanitize($_POST['divorcee_name']) : '';
    $divorcee_father_name = isset($_POST['divorcee_father_name']) ? sanitize($_POST['divorcee_father_name']) : '';
    $divorcee_grandfather_name = isset($_POST['divorcee_grandfather_name']) ? sanitize($_POST['divorcee_grandfather_name']) : '';
    $divorcee_nationality = isset($_POST['divorcee_nationality']) ? sanitize($_POST['divorcee_nationality']) : 'Ethiopian';
    
    // Divorcé (Husband) Information
    $divorcer_name = isset($_POST['divorcer_name']) ? sanitize($_POST['divorcer_name']) : '';
    $divorcer_father_name = isset($_POST['divorcer_father_name']) ? sanitize($_POST['divorcer_father_name']) : '';
    $divorcer_grandfather_name = isset($_POST['divorcer_grandfather_name']) ? sanitize($_POST['divorcer_grandfather_name']) : '';
    $divorcer_nationality = isset($_POST['divorcer_nationality']) ? sanitize($_POST['divorcer_nationality']) : 'Ethiopian';
    
    // Divorce Information
    $divorce_month = isset($_POST['divorce_month']) ? sanitize($_POST['divorce_month']) : '';
    $divorce_day = isset($_POST['divorce_day']) ? (int)$_POST['divorce_day'] : 0;
    $divorce_year = isset($_POST['divorce_year']) ? (int)$_POST['divorce_year'] : 0;
    $divorce_place = isset($_POST['divorce_place']) ? sanitize($_POST['divorce_place']) : '';
    $court_name = isset($_POST['court_name']) ? sanitize($_POST['court_name']) : '';
    $decree_number = isset($_POST['decree_number']) ? sanitize($_POST['decree_number']) : '';
    
    // Dates
    $divorce_registration_date = isset($_POST['divorce_registration_date']) ? sanitize($_POST['divorce_registration_date']) : null;
    $certificate_issue_date = isset($_POST['certificate_issue_date']) ? sanitize($_POST['certificate_issue_date']) : null;
    
    // Create divorce date
    $month_num = 0;
    $months = ['January'=>1,'February'=>2,'March'=>3,'April'=>4,'May'=>5,'June'=>6,'July'=>7,'August'=>8,'September'=>9,'October'=>10,'November'=>11,'December'=>12];
    if (isset($months[$divorce_month])) {
        $month_num = $months[$divorce_month];
    }
    $divorce_date = $divorce_year . '-' . sprintf('%02d', $month_num) . '-' . sprintf('%02d', $divorce_day);
    
    $registration_number = generateRegistrationNumber('DIV');
    
    // Check existing columns
    $check_columns = mysqli_query($conn, "SHOW COLUMNS FROM divorce_registrations");
    $existing_columns = [];
    while ($col = mysqli_fetch_assoc($check_columns)) {
        $existing_columns[] = $col['Field'];
    }
    
    // Build insert query
    $columns = ['user_id', 'husband_name', 'wife_name', 'divorce_date', 'decree_number', 'registration_number', 'status'];
    $values = [$user_id, $divorcer_name, $divorcee_name, $divorce_date, $decree_number, $registration_number, 'pending'];
    
    // Add parties photo if uploaded
    if ($parties_photo_path && in_array('parties_photo', $existing_columns)) {
        $columns[] = 'parties_photo';
        $values[] = $parties_photo_path;
    }
    
    if (in_array('divorce_form_number', $existing_columns) && !empty($divorce_form_number)) {
        $columns[] = 'divorce_form_number';
        $values[] = $divorce_form_number;
    }
    if (in_array('divorce_unique_id', $existing_columns) && !empty($divorce_unique_id)) {
        $columns[] = 'divorce_unique_id';
        $values[] = $divorce_unique_id;
    }
    if (in_array('husband_father_name', $existing_columns) && !empty($divorcer_father_name)) {
        $columns[] = 'husband_father_name';
        $values[] = $divorcer_father_name;
    }
    if (in_array('husband_grandfather_name', $existing_columns) && !empty($divorcer_grandfather_name)) {
        $columns[] = 'husband_grandfather_name';
        $values[] = $divorcer_grandfather_name;
    }
    if (in_array('wife_father_name', $existing_columns) && !empty($divorcee_father_name)) {
        $columns[] = 'wife_father_name';
        $values[] = $divorcee_father_name;
    }
    if (in_array('wife_grandfather_name', $existing_columns) && !empty($divorcee_grandfather_name)) {
        $columns[] = 'wife_grandfather_name';
        $values[] = $divorcee_grandfather_name;
    }
    if (in_array('court_name', $existing_columns) && !empty($court_name)) {
        $columns[] = 'court_name';
        $values[] = $court_name;
    }
    if (in_array('divorce_place', $existing_columns) && !empty($divorce_place)) {
        $columns[] = 'divorce_place';
        $values[] = $divorce_place;
    }
    if (in_array('divorce_registration_date', $existing_columns) && !empty($divorce_registration_date)) {
        $columns[] = 'divorce_registration_date';
        $values[] = $divorce_registration_date;
    }
    if (in_array('certificate_issue_date', $existing_columns) && !empty($certificate_issue_date)) {
        $columns[] = 'certificate_issue_date';
        $values[] = $certificate_issue_date;
    }
    
    $placeholders = implode(',', array_fill(0, count($values), '?'));
    $sql = "INSERT INTO divorce_registrations (" . implode(',', $columns) . ") VALUES (" . $placeholders . ")";
    
    $stmt = mysqli_prepare($conn, $sql);
    $types = 'i' . str_repeat('s', count($values) - 1);
    
    $bind_params = [$types];
    for ($i = 0; $i < count($values); $i++) {
        $bind_params[] = &$values[$i];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Divorce registration submitted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
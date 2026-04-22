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
    
    // Get form data
    $child_name = isset($_POST['child_name']) ? sanitize($_POST['child_name']) : '';
    $child_father_name = isset($_POST['child_father_name']) ? sanitize($_POST['child_father_name']) : '';
    $child_grandfather_name = isset($_POST['child_grandfather_name']) ? sanitize($_POST['child_grandfather_name']) : '';
    $sn = isset($_POST['sn']) ? sanitize($_POST['sn']) : '';
    $birth_month = isset($_POST['birth_month']) ? sanitize($_POST['birth_month']) : '';
    $birth_day = isset($_POST['birth_day']) ? (int)$_POST['birth_day'] : 0;
    $birth_year = isset($_POST['birth_year']) ? (int)$_POST['birth_year'] : 0;
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $nationality = isset($_POST['nationality']) ? sanitize($_POST['nationality']) : 'Ethiopian';
    $zonal_admin = isset($_POST['zonal_admin']) ? sanitize($_POST['zonal_admin']) : '';
    $birth_place = isset($_POST['birth_place']) ? sanitize($_POST['birth_place']) : '';
    $region_admin = isset($_POST['region_admin']) ? sanitize($_POST['region_admin']) : '';
    $woreda = isset($_POST['woreda']) ? sanitize($_POST['woreda']) : '';
    $birth_nationality = isset($_POST['birth_nationality']) ? sanitize($_POST['birth_nationality']) : 'Ethiopian';
    $mother_name = isset($_POST['mother_name']) ? sanitize($_POST['mother_name']) : '';
    $mother_nationality = isset($_POST['mother_nationality']) ? sanitize($_POST['mother_nationality']) : 'Ethiopian';
    $father_name = isset($_POST['father_name']) ? sanitize($_POST['father_name']) : '';
    $father_nationality = isset($_POST['father_nationality']) ? sanitize($_POST['father_nationality']) : 'Ethiopian';
    
    // ============================================
    // HANDLE CHILD PHOTO UPLOAD (3x4)
    // ============================================
    $child_photo_path = null;
    if (isset($_FILES['child_photo']) && $_FILES['child_photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['child_photo']['name'];
        $fileext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($fileext, $allowed)) {
            // Create uploads directory if not exists
            $upload_dir = '../assets/uploads/birth_photos/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $new_filename = 'birth_' . $user_id . '_' . time() . '.' . $fileext;
            $upload_path = $upload_dir . $new_filename;
            
            // Resize image to 3x4 proportions (300x400 pixels for 3x4 cm)
            $src = null;
            if ($fileext == 'jpg' || $fileext == 'jpeg') {
                $src = imagecreatefromjpeg($_FILES['child_photo']['tmp_name']);
            } elseif ($fileext == 'png') {
                $src = imagecreatefrompng($_FILES['child_photo']['tmp_name']);
            }
            
            if ($src) {
                // Get original dimensions
                $orig_width = imagesx($src);
                $orig_height = imagesy($src);
                
                // Target dimensions for 3x4 (300x400 pixels)
                $target_width = 300;
                $target_height = 400;
                
                // Create new image
                $dst = imagecreatetruecolor($target_width, $target_height);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $target_width, $target_height, $orig_width, $orig_height);
                
                // Save resized image
                if ($fileext == 'jpg' || $fileext == 'jpeg') {
                    imagejpeg($dst, $upload_path, 90);
                } else {
                    imagepng($dst, $upload_path, 9);
                }
                
                imagedestroy($src);
                imagedestroy($dst);
                
                $child_photo_path = 'assets/uploads/birth_photos/' . $new_filename;
            } else {
                // Fallback: just move the file without resizing
                move_uploaded_file($_FILES['child_photo']['tmp_name'], $upload_path);
                $child_photo_path = 'assets/uploads/birth_photos/' . $new_filename;
            }
        }
    }
    
    // Validate required fields
    if (empty($child_name) || empty($mother_name) || empty($father_name)) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
        exit();
    }
    
    // Create birth date
    $month_num = 0;
    $months = ['January'=>1,'February'=>2,'March'=>3,'April'=>4,'May'=>5,'June'=>6,'July'=>7,'August'=>8,'September'=>9,'October'=>10,'November'=>11,'December'=>12];
    if (isset($months[$birth_month])) {
        $month_num = $months[$birth_month];
    }
    $birth_date = $birth_year . '-' . sprintf('%02d', $month_num) . '-' . sprintf('%02d', $birth_day);
    
    $gender = isset($_POST['gender']) ? sanitize($_POST['gender']) : 'Male';
    $registration_number = generateRegistrationNumber('BIR');
    
    // Check existing columns in birth_registrations
    $check_columns = mysqli_query($conn, "SHOW COLUMNS FROM birth_registrations");
    $existing_columns = [];
    while ($col = mysqli_fetch_assoc($check_columns)) {
        $existing_columns[] = $col['Field'];
    }
    
    // Build insert query based on existing columns
    $columns = ['user_id', 'child_name', 'mother_name', 'father_name', 'birth_date', 'gender', 'registration_number', 'status'];
    $values = [$user_id, $child_name, $mother_name, $father_name, $birth_date, $gender, $registration_number, 'pending'];
    
    // Add child photo if uploaded
    if ($child_photo_path && in_array('child_photo', $existing_columns)) {
        $columns[] = 'child_photo';
        $values[] = $child_photo_path;
    }
    
    // Add optional columns if they exist in the table
    if (in_array('child_father_name', $existing_columns) && !empty($child_father_name)) {
        $columns[] = 'child_father_name';
        $values[] = $child_father_name;
    }
    if (in_array('child_grandfather_name', $existing_columns) && !empty($child_grandfather_name)) {
        $columns[] = 'child_grandfather_name';
        $values[] = $child_grandfather_name;
    }
    if (in_array('sn', $existing_columns) && !empty($sn)) {
        $columns[] = 'sn';
        $values[] = $sn;
    }
    if (in_array('birth_month', $existing_columns) && !empty($birth_month)) {
        $columns[] = 'birth_month';
        $values[] = $birth_month;
    }
    if (in_array('birth_day', $existing_columns) && !empty($birth_day)) {
        $columns[] = 'birth_day';
        $values[] = $birth_day;
    }
    if (in_array('birth_year', $existing_columns) && !empty($birth_year)) {
        $columns[] = 'birth_year';
        $values[] = $birth_year;
    }
    if (in_array('phone', $existing_columns) && !empty($phone)) {
        $columns[] = 'phone';
        $values[] = $phone;
    }
    if (in_array('nationality', $existing_columns) && !empty($nationality)) {
        $columns[] = 'nationality';
        $values[] = $nationality;
    }
    if (in_array('zonal_admin', $existing_columns) && !empty($zonal_admin)) {
        $columns[] = 'zonal_admin';
        $values[] = $zonal_admin;
    }
    if (in_array('birth_place', $existing_columns) && !empty($birth_place)) {
        $columns[] = 'birth_place';
        $values[] = $birth_place;
    }
    if (in_array('region_admin', $existing_columns) && !empty($region_admin)) {
        $columns[] = 'region_admin';
        $values[] = $region_admin;
    }
    if (in_array('woreda', $existing_columns) && !empty($woreda)) {
        $columns[] = 'woreda';
        $values[] = $woreda;
    }
    if (in_array('birth_nationality', $existing_columns) && !empty($birth_nationality)) {
        $columns[] = 'birth_nationality';
        $values[] = $birth_nationality;
    }
    if (in_array('mother_nationality', $existing_columns) && !empty($mother_nationality)) {
        $columns[] = 'mother_nationality';
        $values[] = $mother_nationality;
    }
    if (in_array('father_nationality', $existing_columns) && !empty($father_nationality)) {
        $columns[] = 'father_nationality';
        $values[] = $father_nationality;
    }
    
    // Build the query
    $placeholders = implode(',', array_fill(0, count($values), '?'));
    $sql = "INSERT INTO birth_registrations (" . implode(',', $columns) . ") VALUES (" . $placeholders . ")";
    
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
        echo json_encode(['success' => true, 'message' => 'Birth registration submitted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
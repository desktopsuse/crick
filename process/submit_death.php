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
    // HANDLE DECEASED PHOTO UPLOAD (3x4)
    // ============================================
    $deceased_photo_path = null;
    if (isset($_FILES['deceased_photo']) && $_FILES['deceased_photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['deceased_photo']['name'];
        $fileext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($fileext, $allowed)) {
            $upload_dir = '../assets/uploads/death_photos/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_filename = 'death_' . $user_id . '_' . time() . '.' . $fileext;
            $upload_path = $upload_dir . $new_filename;

            // Resize to 300x400 (3x4 cm proportions)
            $src = null;
            if ($fileext == 'jpg' || $fileext == 'jpeg') {
                $src = imagecreatefromjpeg($_FILES['deceased_photo']['tmp_name']);
            } elseif ($fileext == 'png') {
                $src = imagecreatefrompng($_FILES['deceased_photo']['tmp_name']);
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

                $deceased_photo_path = 'assets/uploads/death_photos/' . $new_filename;
            } else {
                move_uploaded_file($_FILES['deceased_photo']['tmp_name'], $upload_path);
                $deceased_photo_path = 'assets/uploads/death_photos/' . $new_filename;
            }
        }
    }

    // Get form data
    $death_form_number = isset($_POST['death_form_number']) ? sanitize($_POST['death_form_number']) : '';
    $death_unique_id = isset($_POST['death_unique_id']) ? sanitize($_POST['death_unique_id']) : '';
    $deceased_name = isset($_POST['deceased_name']) ? sanitize($_POST['deceased_name']) : '';
    $deceased_father_name = isset($_POST['deceased_father_name']) ? sanitize($_POST['deceased_father_name']) : '';
    $deceased_grandfather_name = isset($_POST['deceased_grandfather_name']) ? sanitize($_POST['deceased_grandfather_name']) : '';
    $title = isset($_POST['title']) ? sanitize($_POST['title']) : '';
    $sex = isset($_POST['sex']) ? sanitize($_POST['sex']) : '';
    $nationality = isset($_POST['nationality']) ? sanitize($_POST['nationality']) : 'Ethiopian';
    $place_of_death = isset($_POST['place_of_death']) ? sanitize($_POST['place_of_death']) : '';
    $death_month = isset($_POST['death_month']) ? sanitize($_POST['death_month']) : '';
    $death_day = isset($_POST['death_day']) ? (int)$_POST['death_day'] : 0;
    $death_year = isset($_POST['death_year']) ? (int)$_POST['death_year'] : 0;
    $cause_of_death = isset($_POST['cause_of_death']) ? sanitize($_POST['cause_of_death']) : '';
    $death_place = isset($_POST['death_place']) ? sanitize($_POST['death_place']) : '';
    $death_registration_date = isset($_POST['death_registration_date']) ? sanitize($_POST['death_registration_date']) : null;
    $certificate_issue_date = isset($_POST['certificate_issue_date']) ? sanitize($_POST['certificate_issue_date']) : null;
    $registrar_name = isset($_POST['registrar_name']) ? sanitize($_POST['registrar_name']) : '';
    $registrar_father_name = isset($_POST['registrar_father_name']) ? sanitize($_POST['registrar_father_name']) : '';
    $registrar_grandfather_name = isset($_POST['registrar_grandfather_name']) ? sanitize($_POST['registrar_grandfather_name']) : '';

    // Create death date (FIXED: changed date_of_death to death_date)
    $month_num = 0;
    $months = ['January' => 1, 'February' => 2, 'March' => 3, 'April' => 4, 'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8, 'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12];
    if (isset($months[$death_month])) {
        $month_num = $months[$death_month];
    }
    $death_date = $death_year . '-' . sprintf('%02d', $month_num) . '-' . sprintf('%02d', $death_day);

    $certificate_number = isset($_POST['certificate_number']) ? sanitize($_POST['certificate_number']) : '';
    $registration_number = generateRegistrationNumber('DTH');

    // Check existing columns
    $check_columns = mysqli_query($conn, "SHOW COLUMNS FROM death_registrations");
    $existing_columns = [];
    while ($col = mysqli_fetch_assoc($check_columns)) {
        $existing_columns[] = $col['Field'];
    }

    // Build insert query (FIXED: using death_date instead of date_of_death)
    $columns = ['user_id', 'deceased_name', 'death_date', 'certificate_number', 'registration_number', 'status'];
    $values = [$user_id, $deceased_name, $death_date, $certificate_number, $registration_number, 'pending'];

    // Add deceased photo if uploaded
    if ($deceased_photo_path && in_array('deceased_photo', $existing_columns)) {
        $columns[] = 'deceased_photo';
        $values[] = $deceased_photo_path;
    }

    if (in_array('death_form_number', $existing_columns) && !empty($death_form_number)) {
        $columns[] = 'death_form_number';
        $values[] = $death_form_number;
    }
    if (in_array('death_unique_id', $existing_columns) && !empty($death_unique_id)) {
        $columns[] = 'death_unique_id';
        $values[] = $death_unique_id;
    }
    if (in_array('deceased_father_name', $existing_columns) && !empty($deceased_father_name)) {
        $columns[] = 'deceased_father_name';
        $values[] = $deceased_father_name;
    }
    if (in_array('deceased_grandfather_name', $existing_columns) && !empty($deceased_grandfather_name)) {
        $columns[] = 'deceased_grandfather_name';
        $values[] = $deceased_grandfather_name;
    }
    if (in_array('title', $existing_columns) && !empty($title)) {
        $columns[] = 'title';
        $values[] = $title;
    }
    if (in_array('sex', $existing_columns) && !empty($sex)) {
        $columns[] = 'sex';
        $values[] = $sex;
    }
    if (in_array('nationality', $existing_columns) && !empty($nationality)) {
        $columns[] = 'nationality';
        $values[] = $nationality;
    }
    if (in_array('place_of_death', $existing_columns) && !empty($place_of_death)) {
        $columns[] = 'place_of_death';
        $values[] = $place_of_death;
    }
    if (in_array('cause_of_death', $existing_columns) && !empty($cause_of_death)) {
        $columns[] = 'cause_of_death';
        $values[] = $cause_of_death;
    }
    if (in_array('death_place', $existing_columns) && !empty($death_place)) {
        $columns[] = 'death_place';
        $values[] = $death_place;
    }
    if (in_array('death_registration_date', $existing_columns) && !empty($death_registration_date)) {
        $columns[] = 'death_registration_date';
        $values[] = $death_registration_date;
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

    $placeholders = implode(',', array_fill(0, count($values), '?'));
    $sql = "INSERT INTO death_registrations (" . implode(',', $columns) . ") VALUES (" . $placeholders . ")";

    $stmt = mysqli_prepare($conn, $sql);
    $types = 'i' . str_repeat('s', count($values) - 1);

    $bind_params = [$types];
    for ($i = 0; $i < count($values); $i++) {
        $bind_params[] = &$values[$i];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_params);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Death registration submitted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

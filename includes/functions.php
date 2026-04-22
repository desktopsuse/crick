<?php
// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Get user data by ID
function getUserById($user_id)
{
    global $conn;
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Generate unique registration number
function generateRegistrationNumber($prefix)
{
    return $prefix . '-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
}

// Get status badge HTML
function getStatusBadge($status)
{
    switch ($status) {
        case 'approved':
            return '<span class="status-approved fw-bold text-info">Approved</span>';
        case 'rejected':
            return '<span class="status-rejected text-danger">Rejected</span>';
        default:
            return '<span class="status-pending text-secondary">Pending</span>';
    }
}

// Get user's registrations count
function getUserRegistrationsCount($user_id, $type)
{
    global $conn;
    $table = $type . '_registrations';
    $sql = "SELECT COUNT(*) as count FROM $table WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// Redirect if not logged in
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login_simple.php');
        exit();
    }
}

// Redirect if not admin
function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}

// Sanitize input
function sanitize($input)
{
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($input))));
}

// ============================================
// GET CURRENT LANGUAGE
// ============================================
function getCurrentLanguage()
{
    if (isset($_SESSION['language'])) {
        return $_SESSION['language'];
    }
    if (isset($_COOKIE['language'])) {
        return $_COOKIE['language'];
    }
    return 'en';
}

// ============================================
// SET LANGUAGE
// ============================================
function setLanguage($lang)
{
    $_SESSION['language'] = $lang;
    setcookie('language', $lang, time() + (86400 * 30), "/");
}

// ============================================
// LANGUAGE TRANSLATION FUNCTION (COMPLETE)
// ============================================
function getTranslation($key, $lang = null)
{
    if ($lang === null) {
        $lang = getCurrentLanguage();
    }

    $translations = [
        'en' => [
            // Navigation
            'home' => 'Home',
            'about' => 'About',
            'contact' => 'Contact',
            'dashboard' => 'Dashboard',
            'profile' => 'Profile',
            'login' => 'Login',
            'register' => 'Register',
            'logout' => 'Logout',

            // Hero Section
            'hero_title' => 'Ethiopia Vital Events System',
            'hero_subtitle' => 'The unified digital sanctuary for civil registration. Securely manage life\'s most important milestones.',
            'get_started' => 'Get Started',
            'learn_more' => 'Learn More',
            'welcome_back' => 'Welcome back,',

            // Services
            'services_title' => 'Our Services',
            'birth_registration' => 'Birth Registration',
            'marriage_registration' => 'Marriage Registration',
            'divorce_registration' => 'Divorce Registration',
            'death_registration' => 'Death Registration',
            'register_new_birth' => 'Register a new birth',
            'register_marriage' => 'Register a marriage',
            'register_divorce' => 'Register a divorce',
            'register_death' => 'Register a death',

            // About Page
            'about_title' => 'About Vital Events Registration',
            'about_subtitle' => 'A modern civil registration system led by the Ethiopia Federal Vital Events Registration Agency',
            'legal_framework' => 'Legal Framework',
            'legal_desc' => 'This directive is issued under Proclamation No. 760/2004 (as amended) on Vital Events Registration and National Identification.',
            'legal_desc2' => 'The proclamation guides vital events registration for Ethiopians abroad, refugees, the Ethiopian Maritime Transport & Logistics Service, and compulsory defense forces.',
            'our_mission' => 'Our Mission',
            'mission_desc' => 'The Ethiopia Federal Vital Events Registration Agency is committed to building a reliable, modern, and accessible civil registration system.',
            'registration_requirements' => 'Registration Requirements',
            'birth_requirements' => 'Birth Registration Requirements',
            'marriage_requirements' => 'Marriage Registration Requirements',
            'divorce_requirements' => 'Divorce Registration Requirements',
            'death_requirements' => 'Death Registration Requirements',
            'requirement_1' => 'Valid residence permit, national ID, or passport',
            'requirement_2' => 'Certificate from a health institution',
            'requirement_3' => 'Legal guardianship evidence (if applicable)',
            'requirement_4' => 'Registration is only for live births',
            'requirement_5' => 'Both parties must be at least 18 years old',
            'requirement_6' => 'Identity documents required',
            'requirement_7' => 'Four witnesses (2 from each side)',
            'requirement_8' => 'Previous marriage certificate (if applicable)',
            'requirement_9' => 'Court decree required',
            'requirement_10' => 'Valid identification for both parties',
            'requirement_11' => 'Medical certificate of death',
            'requirement_12' => 'Valid ID of the deceased',
            'registration_principles' => 'Registration Principles',
            'principle_1' => 'Record Keeping',
            'principle_1_desc' => 'Storage areas must have adequate ventilation, free from humidity and direct sunlight.',
            'principle_2' => 'Security',
            'principle_2_desc' => 'Adequate protection against theft and loss must be provided.',
            'principle_3' => 'Organization',
            'principle_3_desc' => 'Records must be organized by event type, location, year, and registration number.',
            'register_today' => 'Register Today',
            'register_today_desc' => 'Digitally register your vital life events and access National ID services',

            // Contact Page
            'contact_title' => 'Contact Us',
            'contact_subtitle' => 'If you have any questions or need assistance, don\'t hesitate to reach out to us',
            'office_address' => 'Office Address',
            'office_address_text' => 'Addis Ababa, Ethiopia<br>Bole Road, Vital Events Building<br>Code: 1234',
            'phone_title' => 'Phone',
            'phone_text' => '+251-11-123-4567<br>+251-11-765-4321<br>Mon - Fri 8:30 AM - 5:30 PM',
            'email_title' => 'Email',
            'email_text' => 'info@vitalevents.gov.et<br>support@vitalevents.gov.et<br>complaints@vitalevents.gov.et',
            'send_message' => 'Send Us a Message',
            'your_name' => 'Your Name',
            'your_email' => 'Your Email',
            'your_phone' => 'Your Phone',
            'subject' => 'Subject',
            'your_message' => 'Your Message',
            'send' => 'Send Message',
            'emergency_support' => 'Emergency Support',
            'emergency_desc' => 'For customers needing immediate assistance:',
            'gallery' => 'Our Gallery',
            'gallery_subtitle' => 'Moments captured from our vital events registration services',

            // Dashboard
            'recent_title' => 'Recent Registrations',
            'col_name' => 'Name',
            'col_type' => 'Type',
            'col_date' => 'Date',
            'no_registrations' => 'No registrations yet',

            // Profile
            'edit_profile' => 'Edit Profile Information',
            'full_name' => 'Full Name',
            'email' => 'Email Address',
            'phone' => 'Phone Number',
            'national_id' => 'National ID Number',
            'address' => 'Address',
            'save_changes' => 'Save Changes',
            'my_certificates' => 'My Certificates',
            'certificate' => 'Certificate',
            'certificate_number' => 'Certificate Number',
            'name' => 'Name',
            'issued_date' => 'Issued Date',
            'approved' => 'Approved',
            'pending' => 'Pending',
            'rejected' => 'Rejected',
            'not_provided' => 'Not provided',
            'no_certificates' => 'No certificates found',
            'register_now' => 'Register Now',
            'upload_photo' => 'Upload Profile Photo',
            'choose_photo' => 'Choose Photo',
            'photo_format' => 'Supported formats: JPG, PNG, GIF',
            'upload' => 'Upload',
            'cancel' => 'Cancel',
            'back_to_dashboard' => 'Back to Dashboard',
            'approved_certificates' => 'Approved Certificates',
            'pending_certificates' => 'Pending Certificates',
            'rejected_certificates' => 'Rejected Certificates',
            'reg_number' => 'Registration Number',
            'submitted_date' => 'Submitted Date',
            'pending_message' => 'This certificate is pending review by administrator',
            'rejected_message' => 'This certificate has been rejected. Please contact support.',

            // Stats
            'registered_citizens' => 'Registered Citizens',
            'registration_offices' => 'Registration Offices',
            'online_service' => 'Online Service',
            'digital_integration' => 'Digital Integration',

            // Submit
            'submit' => 'Submit Registration',
            'profile_updated' => 'Profile updated successfully!',
            'registration_success' => 'Registration submitted successfully!',
            'registration_failed' => 'Failed to submit registration',
            'form_error' => 'Please fill all required fields',
            'email_exists' => 'Email already registered',
            'password_mismatch' => 'Passwords do not match',

            // Login/Register Page
            'welcome_to_vital' => 'Welcome to Vital Events',
            'login_description' => 'Please login to access your dashboard and manage your vital events registration',
            'register_description' => 'Create an account to start registering vital events',
            'demo_accounts' => 'Demo Accounts',
            'admin_account' => 'Admin: admin@vitalevents.gov.et / admin123',
            'user_account' => 'User: alemu@example.com / user123',
            'already_have_account' => 'Already have an account?',
            'dont_have_account' => 'Don\'t have an account?',
            'login_here' => 'Login here',
            'register_here' => 'Register here',
            'invalid_password' => 'Invalid password!',
            'email_not_found' => 'Email not found!',
            'email_already_registered' => 'Email already registered!',
            'passwords_do_not_match' => 'Passwords do not match!',
            'password_min_length' => 'Password must be at least 6 characters!',
            'registration_success_message' => 'Registration successful! You can now login.',
            'registration_failed_message' => 'Registration failed! Please try again.',
            'login_success' => 'Login successful! Redirecting...',

            // Footer
            'quick_links' => 'Quick Links',
            'services' => 'Services',
            'contact_info' => 'Contact Info',
            'all_rights_reserved' => 'All rights reserved.',

            // English - Required keys
            'birth_registration' => 'Birth Registration',
            'child_photo' => 'Child Photo',
            'upload_photo' => 'Upload Photo',
            'photo_format' => 'Supported formats',
            'child_info' => 'Child Information',
            'child_name' => 'Child\'s Full Name',
            'father_name' => 'Father\'s Name',
            'grandfather_name' => 'Grand Father\'s Name',
            'birth_details' => 'Birth Details',
            'gender' => 'Gender',
            'male' => 'Male',
            'female' => 'Female',
            'birth_month' => 'Birth Month',
            'birth_date' => 'Birth Date',
            'birth_year' => 'Birth Year',
            'select_month' => 'Select Month',
            'place_of_birth' => 'Place of Birth',
            'birth_place' => 'Place of Birth',
            'kebele' => 'Kebele',
            'region_admin' => 'Region/City Administration',
            'select_region' => 'Select Region',
            'woreda' => 'Woreda/Special Woreda',
            'nationality' => 'Nationality',
            'parents_info' => 'Parents Information',
            'mother_name' => 'Mother\'s Full Name',
            'mother_nationality' => 'Mother\'s Nationality',
            'father_name' => 'Father\'s Full Name',
            'father_nationality' => 'Father\'s Nationality',
            'cancel' => 'Cancel',
            'submit' => 'Submit Registration',

            // English
            'divorcer_birth_registration_unique_id' => 'Divorcer (Husband) Birth Registration Unique ID',
            'divorcee_birth_registration_unique_id' => 'Divorcee (Wife) Birth Registration Unique ID',
            'divorce_date_info' => 'Divorce Date',
            'divorce_place_registration' => 'Place of Divorce Registration',
            'divorce_registration_date_info' => 'Divorce Registration Date',

            // English
            'husband_photo' => 'Husband Photo',
            'wife_photo' => 'Wife Photo',
            'husband_birth_registration_unique_id' => 'Husband Birth Registration Unique ID',
            'wife_birth_registration_unique_id' => 'Wife Birth Registration Unique ID',
            'husband_info' => 'Husband / Groom Information',
            'wife_info' => 'Wife / Bride Information',
            'husband_dob' => 'Husband Date of Birth',
            'wife_dob' => 'Wife Date of Birth',
            'month' => 'Month',
            'date' => 'Date',
            'year' => 'Year',
            'marriage_place_registration' => 'Place of Marriage Registration',
            'region' => 'Region',
            'zone' => 'Zone',
            'city' => 'City',
            'marriage_registration_date' => 'Marriage Registration Date',
            'select_month' => 'Select Month',
            'select_region' => 'Select Region',

            // English
            'death_form_number' => 'Death Register Form Number',
            'death_unique_id' => 'Death Registration Unique ID',
            'deceased_photo' => 'Deceased Photo',
            'title' => 'Title',
            'select_title' => 'Select Title',
            'sex' => 'Sex',
            'select_sex' => 'Select Sex',
            'nationality' => 'Nationality',
            'date_of_birth' => 'Date of Birth (if available)',
            'place_of_death' => 'Place of Death',
            'date_of_death' => 'Date of Death',
            'cause_of_death' => 'Cause of Death',
            'death_registration_date' => 'Date of Death Registration',
            'certificate_number' => 'Medical Certificate Number',
        ],
        'am' => [
            // Navigation
            'home' => 'መነሻ',
            'about' => 'ስለ እኛ',
            'contact' => 'አግኙን',
            'dashboard' => 'ዳሽቦርድ',
            'profile' => 'መገለጫ',
            'login' => 'ግባ',
            'register' => 'ተመዝገብ',
            'logout' => 'ውጣ',

            // Hero Section
            'hero_title' => 'የኢትዮጵያ የወሳኝ ኩነቶች ሥርዓት',
            'hero_subtitle' => 'የተዋሃደ ዲጂታል የሲቪል ምዝገባ መድረክ። ከልደት እስከ ዜግነት ያሉ ጠቃሚ የህይወት ክስተቶችን በደህንነት ያስተዳድሩ።',
            'get_started' => 'ጀምር',
            'learn_more' => 'ተጨማሪ',
            'welcome_back' => 'እንኳን በደህና መጡ፣',

            // Services
            'services_title' => 'አገልግሎቶቻችን',
            'birth_registration' => 'የልደት ምዝገባ',
            'marriage_registration' => 'የጋብቻ ምዝገባ',
            'divorce_registration' => 'የፍቺ ምዝገባ',
            'death_registration' => 'የሞት ምዝገባ',
            'register_new_birth' => 'አዲስ ልደት ይመዝግቡ',
            'register_marriage' => 'ጋብቻ ይመዝግቡ',
            'register_divorce' => 'ፍቺ ይመዝግቡ',
            'register_death' => 'ሞት ይመዝግቡ',

            // About Page
            'about_title' => 'ስለ ወሳኝ ኩነት ምዝገባ',
            'about_subtitle' => 'በኢትዮጵያ ፌዴራል ወሳኝ ኩነት መዝጋቢ ኤጀንሲ የሚመራ ዘመናዊ የሲቪል ምዝገባ ሥርዓት',
            'legal_framework' => 'ህጋዊ ማዕቀፍ',
            'legal_desc' => 'የወሳኝ ኩነት ምዝገባ እና ብሔራዊ መታወቂያ አዋጅ ቁጥር 760/2004 (እንደተሻሻለ) መሰረት ይህ መመሪያ ወጥቷል።',
            'legal_desc2' => 'አዋጁ በውጭ ሀገር ለሚኖሩ ኢትዮጵያውያን፣ ለስደተኞች፣ ለኢትዮጵያ ባህር ትራንስፖርትና ሎጂስቲክስ አገልግሎት ድርጅት እና በግዳጅ ላይ ላሉ መከላከያ ሰራዊት አባላት የወሳኝ ኩነት ምዝገባ አፈጻጸምን ይመራል።',
            'our_mission' => 'ተልዕኮ',
            'mission_desc' => 'የኢትዮጵያ ፌዴራል ወሳኝ ኩነት ምዝገባ ኤጀንሲ አስተማማኝ፣ ዘመናዊ እና ተደራሽ የሲቪል ምዝገባ ሥርዓት ለመገንባት ቁርጠኛ ነው።',
            'registration_requirements' => 'የምዝገባ መስፈርቶች',
            'birth_requirements' => 'የልደት ምዝገባ መስፈርቶች',
            'marriage_requirements' => 'የጋብቻ ምዝገባ መስፈርቶች',
            'divorce_requirements' => 'የፍቺ ምዝገባ መስፈርቶች',
            'death_requirements' => 'የሞት ምዝገባ መስፈርቶች',
            'requirement_1' => 'የሚሰራ የነዋሪነት ፍቃድ፣ ብሄራዊ መታወቂያ ወይም ፓስፖርት',
            'requirement_2' => 'ከጤና ተቋም የተሰጠ የምስክር ወረቀት',
            'requirement_3' => 'ህጋዊ የአሳዳሪነት ማስረጃ (ካለ)',
            'requirement_4' => 'ምዝገባ በህይወት ለተወለዱ ብቻ ነው',
            'requirement_5' => 'ሁለቱም ተጋቢዎች ቢያንስ 18 ዓመት መሞላት አለባቸው',
            'requirement_6' => 'የማንነት መታወቂያ ሰነዶች ያስፈልጋሉ',
            'requirement_7' => 'አራት ምስክሮች (ከእያንዳንዱ ወገን 2)',
            'requirement_8' => 'ከዚህ በፊት ጋብቻ ከነበረ የፍቺ ወይም የሞት ምስክር ወረቀት',
            'requirement_9' => 'የፍርድ ቤት ውሳኔ ያስፈልጋል',
            'requirement_10' => 'የሁለቱም አካላት ልክ መታወቂያ',
            'requirement_11' => 'የህክምና የሞት ምስክር ወረቀት',
            'requirement_12' => 'የሟቹ ልክ መታወቂያ',
            'registration_principles' => 'የምዝገባ መርሆዎች',
            'principle_1' => 'የክብር መዝገብ አጠባበቅ',
            'principle_1_desc' => 'መረጃ የሚከማችበት ክፍል በቂ አየር ያለው፣ ከዕርጥበትና ከቀጥተኛ የወሐይ ጨረር ነፃ የሆነ መሆን አለበት።',
            'principle_2' => 'ደህንነት',
            'principle_2_desc' => 'መረጃዎች እንዳይዘረፉ እና እንዳይሰረቁ በቂ ጥበቃ መደረግ አለበት።',
            'principle_3' => 'ድርጀት',
            'principle_3_desc' => 'መረጃዎች በኩነት አይነት፣ በተመዘገቡበት ቦታ፣ በዓመተ ምህረት እና በተመዝጋቢው መለያ ቁጥር ቅደም ተከተል መደራጀት አለባቸው።',
            'register_today' => 'ዛሬውኑ ይመዝገቡ',
            'register_today_desc' => 'የእርስዎን የህይወት ወሳኝ ክስተቶች በዲጂታል መንገድ ያስመዝግቡ እና የብሔራዊ መታወቂያ አገልግሎት ያግኙ',

            // Contact Page
            'contact_title' => 'አግኙን',
            'contact_subtitle' => 'ማንኛውም ጥያቄ ወይም እገዛ ከፈለጉ ከእኛ ጋር ለመገናኘት አያመንቱ',
            'office_address' => 'ቢሮ አድራሻ',
            'office_address_text' => 'አዲስ አበባ፣ ኢትዮጵያ<br>ቦሌ ሩቅ፣ ወሳኝ ኩነት ምዝገባ ህንጻ<br>ኮድ፡ 1234',
            'phone_title' => 'ስልክ',
            'phone_text' => '+251-11-123-4567<br>+251-11-765-4321<br>ሰኞ - ዓርብ 8:30 ጠዋት - 5:30 ማታ',
            'email_title' => 'ኢሜይል',
            'email_text' => 'info@vitalevents.gov.et<br>support@vitalevents.gov.et<br>complaints@vitalevents.gov.et',
            'send_message' => 'መልእክት ይላኩልን',
            'your_name' => 'ሙሉ ስም',
            'your_email' => 'ኢሜይል',
            'your_phone' => 'ስልክ ቁጥር',
            'subject' => 'ርዕስ',
            'your_message' => 'መልእክት',
            'send' => 'መልእክት ላክ',
            'emergency_support' => 'የአደጋ ጊዜ ድጋፍ',
            'emergency_desc' => 'አፋጣኝ እገዛ ለሚፈልጉ ደንበኞች፦',
            'gallery' => 'የእኛ ማዕከለ-ስዕላት',
            'gallery_subtitle' => 'ከወሳኝ ኩነት ምዝገባ አገልግሎቶቻችን የተወሰዱ አፍታዎች',

            // Dashboard
            'recent_title' => 'የቅርብ ጊዜ ምዝገባዎች',
            'col_name' => 'ስም',
            'col_type' => 'አይነት',
            'col_date' => 'ቀን',
            'no_registrations' => 'ምዝገባ የለም',

            // Profile
            'edit_profile' => 'የመገለጫ መረጃ ያስተካክሉ',
            'full_name' => 'ሙሉ ስም',
            'email' => 'ኢሜይል አድራሻ',
            'phone' => 'ስልክ ቁጥር',
            'national_id' => 'ብሔራዊ መታወቂያ ቁጥር',
            'address' => 'አድራሻ',
            'save_changes' => 'ለውጦችን አስቀምጥ',
            'my_certificates' => 'የምስክር ወረቀቶቼ',
            'certificate' => 'ምስክር ወረቀት',
            'certificate_number' => 'የምስክር ወረቀት ቁጥር',
            'name' => 'ስም',
            'issued_date' => 'የተሰጠበት ቀን',
            'approved' => 'ጸድቋል',
            'pending' => 'በመጠባበቅ ላይ',
            'rejected' => 'ውድቅ',
            'not_provided' => 'አልተሰጠም',
            'no_certificates' => 'ምስክር ወረቀት አልተገኘም',
            'register_now' => 'አሁን ይመዝገቡ',
            'upload_photo' => 'የመገለጫ ፎቶ ይምረጡ',
            'choose_photo' => 'ፎቶ ይምረጡ',
            'photo_format' => 'የሚፈቀዱ ቅርጾች: JPG, PNG, GIF',
            'upload' => 'አስገባ',
            'cancel' => 'ሰርዝ',
            'back_to_dashboard' => 'ወደ ዳሽቦርድ ተመለስ',
            'approved_certificates' => 'የጸደቁ ሰርተፍኬቶች',
            'pending_certificates' => 'በመጠባበቅ ላይ ያሉ',
            'rejected_certificates' => 'ውድቅ የሆኑ',
            'reg_number' => 'የምዝገባ ቁጥር',
            'submitted_date' => 'የቀረበበት ቀን',
            'pending_message' => 'ይህ ሰርተፍኬት በአስተዳዳሪ ግምገማ ላይ ነው',
            'rejected_message' => 'ይህ ሰርተፍኬት ውድቅ ተደርጓል። እባክዎ ድጋፍ ያግኙ',

            // Stats
            'registered_citizens' => 'ተመዝጋቢ ዜጎች',
            'registration_offices' => 'የምዝገባ ጽህፈት ቤቶች',
            'online_service' => 'የመስመር ላይ አገልግሎት',
            'digital_integration' => 'ዲጂታል ውህደት',

            // Submit
            'submit' => 'ምዝገባ አስገባ',
            'profile_updated' => 'መገለጫ በሚገባ ተቀይሯል!',
            'registration_success' => 'ምዝገባ በተሳካ ሁኔታ ቀርቧል!',
            'registration_failed' => 'ምዝገባ ማቅረብ አልተሳካም',
            'form_error' => 'እባክዎ ሁሉንም አስፈላጊ መረጃዎች ይሙሉ',
            'email_exists' => 'ኢሜይል ቀድሞ ተመዝግቧል',
            'password_mismatch' => 'የይለፍ ቃሎች አይዛመዱም',

            // Login/Register Page
            'welcome_to_vital' => 'እንኳን ወደ ቫይታል ኢቨንትስ በደህና መጡ',
            'login_description' => 'እባክዎ ወደ መለያዎ ይግቡ ዳሽቦርድዎን ለመድረስ',
            'register_description' => 'የወሳኝ ኩነቶች ምዝገባ ለመጀመር አካውንት ይፍጠሩ',
            'demo_accounts' => 'የሙከራ መለያዎች',
            'admin_account' => 'አስተዳዳሪ: admin@vitalevents.gov.et / admin123',
            'user_account' => 'ተጠቃሚ: alemu@example.com / user123',
            'already_have_account' => 'አካውንት አለዎት?',
            'dont_have_account' => 'አካውንት የለዎትም?',
            'login_here' => 'እዚህ ይግቡ',
            'register_here' => 'እዚህ ይመዝገቡ',
            'invalid_password' => 'የተሳሳተ የይለፍ ቃል!',
            'email_not_found' => 'ኢሜይል አልተገኘም!',
            'email_already_registered' => 'ኢሜይል ቀድሞ ተመዝግቧል!',
            'passwords_do_not_match' => 'የይለፍ ቃሎች አይዛመዱም!',
            'password_min_length' => 'የይለፍ ቃል ቢያንስ 6 ቁምፊዎች መሆን አለበት!',
            'registration_success_message' => 'ምዝገባ ተሳክቷል! አሁን መግባት ይችላሉ።',
            'registration_failed_message' => 'ምዝገባ አልተሳካም! እባክዎ እንደገና ይሞክሩ።',
            'login_success' => 'መግቢያ ተሳክቷል! እየዘወረን ነው...',

            // Footer
            'quick_links' => 'ፈጣን አገናኞች',
            'services' => 'አገልግሎቶች',
            'contact_info' => 'የእውቂያ መረጃ',
            'all_rights_reserved' => 'ሁሉም መብቶች ተጠብቀዋል።',

            // Amharic - Required keys
            'birth_registration' => 'የልደት ምዝገባ',
            'child_photo' => 'የህጻኑ ፎቶ',
            'upload_photo' => 'ፎቶ ይምረጡ',
            'photo_format' => 'የሚፈቀዱ ቅርጾች',
            'child_info' => 'የህጻን መረጃ',
            'child_name' => 'የልጁ/ልጅቷ ሙሉ ስም',
            'father_name' => 'የአባት ስም',
            'grandfather_name' => 'የአያት ስም',
            'birth_details' => 'የልደት ዝርዝር',
            'gender' => 'ጾታ',
            'male' => 'ወንድ',
            'female' => 'ሴት',
            'birth_month' => 'የልደት ወር',
            'birth_date' => 'የልደት ቀን',
            'birth_year' => 'የልደት ዓመት',
            'select_month' => 'ወር ይምረጡ',
            'place_of_birth' => 'የትውልድ ቦታ',
            'birth_place' => 'የተወለደበት ቦታ',
            'kebele' => 'ቀበሌ',
            'region_admin' => 'ክልል/ከተማ አስተዳደር',
            'select_region' => 'ክልል ይምረጡ',
            'woreda' => 'ወረዳ/ልዩ ወረዳ',
            'nationality' => 'ዜግነት',
            'parents_info' => 'የወላጆች መረጃ',
            'mother_name' => 'የእናት ሙሉ ስም',
            'mother_nationality' => 'የእናት ዜግነት',
            'father_name' => 'የአባት ሙሉ ስም',
            'father_nationality' => 'የአባት ዜግነት',
            'cancel' => 'ሰርዝ',
            'submit' => 'ምዝገባ አስገባ',

            'divorcer_birth_registration_unique_id' => 'የባል ልዩ የልደት ምዝገባ መለያ ቁጥር',
            'divorcee_birth_registration_unique_id' => 'የሚስት ልዩ የልደት ምዝገባ መለያ ቁጥር',
            'divorce_date_info' => 'የፍቺ ቀን',
            'divorce_place_registration' => 'ፍቺው የተፈጸመበት ቦታ',
            'divorce_registration_date_info' => 'ፍቺው የተመዘገበበት ቀን',

            // Amharic
            'husband_photo' => 'የባል ፎቶ',
            'wife_photo' => 'የሚስት ፎቶ',
            'husband_birth_registration_unique_id' => 'የባል ልዩ የልደት ምዝገባ መለያ ቁጥር',
            'wife_birth_registration_unique_id' => 'የሚስት ልዩ የልደት ምዝገባ መለያ ቁጥር',
            'husband_info' => 'የባል መረጃ',
            'wife_info' => 'የሚስት መረጃ',
            'husband_dob' => 'የባል የትውልድ ቀን',
            'wife_dob' => 'የሚስት የትውልድ ቀን',
            'month' => 'ወር',
            'date' => 'ቀን',
            'year' => 'ዓመት',
            'marriage_place_registration' => 'ጋብቻው የተፈጸመበት ቦታ',
            'region' => 'ክልል',
            'zone' => 'ዞን',
            'city' => 'ከተማ',
            'marriage_registration_date' => 'ጋብቻው የተመዘገበበት ቀን',
            'select_month' => 'ወር ይምረጡ',
            'select_region' => 'ክልል ይምረጡ',

            // Amharic
            'death_form_number' => 'የሞት ክብር መዝገብ ቅጽ ቁጥር',
            'death_unique_id' => 'የሞት ምዝገባ ልዩ መለያ ቁጥር',
            'deceased_photo' => 'የሟች ፎቶ',
            'title' => 'ማዕረግ',
            'select_title' => 'ማዕረግ ይምረጡ',
            'sex' => 'ጾታ',
            'select_sex' => 'ጾታ ይምረጡ',
            'nationality' => 'ዜግነት',
            'date_of_birth' => 'የትውልድ ቀን (ካለ)',
            'place_of_death' => 'የሞት ቦታ',
            'date_of_death' => 'የሞት ቀን',
            'cause_of_death' => 'የሞት ምክንያት',
            'death_registration_date' => 'ሞቱ የተመዘገበበት ቀን',
            'certificate_number' => 'የሕክምና ምስክር ወረቀት ቁጥር',
        ]
    ];

    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}

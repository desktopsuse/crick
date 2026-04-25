<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);

    if (empty($name) || empty($email) || empty($message)) {
        $error = getTranslation('form_error');
    } else {
        $sql = "INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $phone, $subject, $message);

        if (mysqli_stmt_execute($stmt)) {
            $success = getTranslation('registration_success');
            $_POST = array();
        } else {
            $error = getTranslation('registration_failed');
        }
    }
}

include 'includes/header.php';
?>

<section class="hero" style="background-image: url('assets/images/home/contact.png');">

    <div class="container">
        <div class="hero-content">
            <div class="hero-text text-center" data-aos="fade-up">
                <h1><?php echo getTranslation('contact_title'); ?></h1>
                <p><?php echo getTranslation('contact_subtitle'); ?></p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4 my-5">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card text-center h-100 p-5">
                    <div class="service-icon mx-auto mb-3">
                        <i class="bi bi-geo-alt-fill fs-2 text-primary"></i>
                    </div>
                    <h5 class="fw-bold"><?php echo getTranslation('office_address'); ?></h5>
                    <p class="text-muted small mb-0"><?php echo getTranslation('office_address_text'); ?></p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card text-center h-100 p-5">
                    <div class="service-icon mx-auto mb-3">
                        <i class="bi bi-telephone-fill fs-2 text-primary"></i>
                    </div>
                    <h5 class="fw-bold"><?php echo getTranslation('phone_title'); ?></h5>
                    <p class="text-muted small mb-0"><?php echo getTranslation('phone_text'); ?></p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card text-center h-100 p-5">
                    <div class="service-icon mx-auto mb-3">
                        <i class="bi bi-envelope-fill fs-2 text-primary"></i>
                    </div>
                    <h5 class="fw-bold"><?php echo getTranslation('email_title'); ?></h5>
                    <p class="text-muted small mb-0"><?php echo getTranslation('email_text'); ?></p>
                </div>
            </div>
        </div>

        <div class="row g-5 my-4">
            <div class="col-lg-7" data-aos="fade-right">
                <div class="card p-5">
                    <h2 class="fs-3 fw-bold p-2 text-center"><?php echo getTranslation('send_message'); ?></h2>

                    <?php if ($error): ?>
                        <div class="message error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="message success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="name" class="form-control" placeholder="<?php echo getTranslation('your_name'); ?> *" value="<?php echo $_POST['name'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="email" class="form-control" placeholder="<?php echo getTranslation('your_email'); ?> *" value="<?php echo $_POST['email'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="tel" name="phone" class="form-control" placeholder="<?php echo getTranslation('your_phone'); ?>" value="<?php echo $_POST['phone'] ?? ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <select name="subject" class="form-select">
                                    <option value="General Inquiry"><?php echo getTranslation('subject'); ?></option>
                                    <option value="Technical Support">Technical Support</option>
                                    <option value="Complaint">Complaint</option>
                                    <option value="Registration Question">Registration Question</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <textarea name="message" class="form-control" rows="5" placeholder="<?php echo getTranslation('your_message'); ?> *" required><?php echo $_POST['message'] ?? ''; ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100"><?php echo getTranslation('send'); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-5" data-aos="fade-left">
                <div class="card">
                    <h2 class="fs-3 fw-bold p-4 text-center">Our Location</h2>
                    <div class="bg-light rounded-4 mb-4 d-flex align-items-center justify-content-center min-h-250 " style="min-height: 250px;">
                        <div class="text-center">
                            <i class="bi bi-map fs-1 text-primary opacity-50"></i>
                            <p class="text-muted small mt-2">Addis Ababa, Ethiopia</p>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-4 px-3">Social Media</h5>
                    <div class="d-flex gap-3">
                        <a href="https://www.youtube.com/@techno_computer15" class="btn btn-outline-danger" style="width: 40px; height: 40px;" target="_blank">
                            <i class="bi bi-youtube"></i>
                        </a>
                        <a href="https://www.linkedin.com/in/bereket-fentaw-2158833a9" class="btn btn-outline-primary" style="width: 40px; height: 40px;"><i class="bi bi-linkedin"></i></a>
                        <a href="https://t.me/be0830" class="btn btn-outline-primary" style="width: 40px; height: 40px;"><i class="bi bi-telegram"></i></a>
                        <a href="https://github.com/beky08/vital-events-system.git" class="btn btn-outline-primary rounded-circle" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;" title="View Project on GitHub"> <i class="bi bi-github"></i></a>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 1000,
        once: true
    });
</script>

<?php include 'includes/footer.php'; ?>
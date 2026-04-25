<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
include 'includes/header.php';
?>

    
 <section class="hero" style="background-image: url('assets/images/home/about1.jpg');">


    <div class="container">
        <div class="hero-content">
            <div class="hero-text text-center" data-aos="fade-up">
                <h1><?php echo getTranslation('about_title'); ?></h1>
                <p><?php echo getTranslation('about_subtitle'); ?></p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4 my-5">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="card h-100 p-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="service-icon">
                            <i class="bi bi-file-law fs-2 text-primary"></i>
                        </div>
                        <h2 class="fs-3 fw-bold mb-0"><?php echo getTranslation('legal_framework'); ?></h2>
                    </div>
                    <p><?php echo getTranslation('legal_desc'); ?></p>
                    <p class="mt-3"><?php echo getTranslation('legal_desc2'); ?></p>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="card h-100 p-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="service-icon">
                            <i class="bi bi-shield-check fs-2 text-success"></i>
                        </div>
                        <h2 class="fs-3 fw-bold mb-0"><?php echo getTranslation('our_mission'); ?></h2>
                    </div>
                    <p><?php echo getTranslation('mission_desc'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background: var(--primary-soft);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2><?php echo getTranslation('registration_requirements'); ?></h2>
        </div>
        <div class="row g-4 my-5">
            <div class="col-md-6" data-aos="fade-right">
                <div class="card h-100 p-5">
                    <h5 class="fw-bold mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> <?php echo getTranslation('birth_requirements'); ?></h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-check-lg text-primary me-2"></i> <?php echo getTranslation('requirement_1'); ?></li>
                        <li class="mb-2"><i class="bi bi-check-lg text-primary me-2"></i> <?php echo getTranslation('requirement_2'); ?></li>
                        <li class="mb-2"><i class="bi bi-check-lg text-primary me-2"></i> <?php echo getTranslation('requirement_3'); ?></li>
                        <li class="mb-2"><i class="bi bi-check-lg text-primary me-2"></i> <?php echo getTranslation('requirement_4'); ?></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-left">
                <div class="card h-100 p-5">
                    <h5 class="fw-bold mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> <?php echo getTranslation('marriage_requirements'); ?></h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-check-lg text-primary me-2"></i> <?php echo getTranslation('requirement_5'); ?></li>
                        <li class="mb-2"><i class="bi bi-check-lg text-primary me-2"></i> <?php echo getTranslation('requirement_6'); ?></li>
                        <li class="mb-2"><i class="bi bi-check-lg text-primary me-2"></i> <?php echo getTranslation('requirement_7'); ?></li>
                        <li class="mb-2"><i class="bi bi-check-lg text-primary me-2"></i> <?php echo getTranslation('requirement_8'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-5 my-5" data-aos="fade-right">
                <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDDkjNxsIDSqNSM7dJvvD0l_QmkW1k8qBd0NhD9wbANdiGWS58bbJLcZzSuHUxN1YsB00wngA2E5PU6dRPh9Fkaf9zEJbiLquAT6QjKQVGP8zboaqokm4mOn0eVsIHEm-ZxxicdFLLNICwTH9IcrZwB9ImpEh4QMi5Dze3A_D7dBDNUzB2oqcA4xmgMRrZqcsHBoiyumOkJ8qPFJP2Od8LhYnOWz4iDlKYIyIYeXr5GccjdVelGRcwhDH42Pej2vAWrldIZZzy_kfw" class="img-fluid rounded-4 shadow-sm" alt="Registration process">
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <h2 class="fs-2 fw-bold mb-4"><?php echo getTranslation('registration_principles'); ?></h2>
                <div class="d-flex mb-3">
                    <div class="step-circle">1</div>
                    <div><strong><?php echo getTranslation('principle_1'); ?></strong><p class="text-muted small mb-0"><?php echo getTranslation('principle_1_desc'); ?></p></div>
                </div>
                <div class="d-flex mb-3">
                    <div class="step-circle">2</div>
                    <div><strong><?php echo getTranslation('principle_2'); ?></strong><p class="text-muted small mb-0"><?php echo getTranslation('principle_2_desc'); ?></p></div>
                </div>
                <div class="d-flex mb-3">
                    <div class="step-circle">3</div>
                    <div><strong><?php echo getTranslation('principle_3'); ?></strong><p class="text-muted small mb-0"><?php echo getTranslation('principle_3_desc'); ?></p></div>
                </div>
            </div>
        </div>
    </div>
</section>


<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ duration: 1000, once: true });</script>

<?php include 'includes/footer.php'; ?>
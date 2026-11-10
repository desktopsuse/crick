<?php
require_once 'includes/config.php';  // used to create connection database
require_once 'includes/functions.php';
include 'includes/header.php';

?>

<!-- Hero Section with Full Screen Background Image -->
<section class="hero" style="background-image: url('assets/images/home/Page 2.jpeg');">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text" data-aos="fade-right">
                <h1><?php echo getTranslation('hero_title'); ?></h1>
                <p><?php echo getTranslation('hero_subtitle'); ?></p>
                <div class="hero-buttons">
                    <a href="register_simple.php" class="btn btn-primary"><?php echo getTranslation('get_started'); ?></a>
                    <a href="about.php" class="btn btn-outline"><?php echo getTranslation('learn_more'); ?></a>
                </div>
            </div>

            <div class="hero-login-card" data-aos="fade-left">
                <i class="bi bi-shield-check fs-1 text-primary d-block text-center mb-3"></i>
                <h3><?php echo getTranslation('welcome_to_vital'); ?></h3>
                <p><?php echo getTranslation('login_description'); ?></p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn btn-primary"><?php echo getTranslation('dashboard'); ?></a>
                <?php else: ?>
                    <a href="login_simple.php" class="btn btn-primary"><?php echo getTranslation('login'); ?></a>
                    <a href="register_simple.php" class="btn btn-outline"><?php echo getTranslation('register'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2><?php echo getTranslation('services_title'); ?></h2>
            <p>Register life's most important events securely and efficiently</p>
        </div>

        <div class="services-grid">
            <div class="service-card" data-service="birth" data-aos="fade-up" data-aos-delay="100" style="background-image: url('assets/images/birth/births.jpg');">
                <div class="service-content">
                    <div class="service-icon">
                        <i class="bi bi-gift-fill"></i>
                    </div>
                    <h3><?php echo getTranslation('birth_registration'); ?></h3>
                    <p><?php echo getTranslation('register_new_birth'); ?></p>
                </div>
            </div>

            <div class="service-card" data-service="marriage" data-aos="fade-up" data-aos-delay="200" style="background-image: url('assets/images/marriage/mariage.jpeg');">
                <div class="service-content">
                    <div class="service-icon">
                        <i class="bi bi-suit-heart-fill"></i>
                    </div>
                    <h3><?php echo getTranslation('marriage_registration'); ?></h3>
                    <p><?php echo getTranslation('register_marriage'); ?></p>
                </div>
            </div>

            <div class="service-card" data-service="divorce" data-aos="fade-up" data-aos-delay="300" style="background-image: url('assets/images/divorce/divorce.jpg');">
                <div class="service-content">
                    <div class="service-icon">
                        <i class="bi bi-heartbreak-fill"></i>
                    </div>
                    <h3><?php echo getTranslation('divorce_registration'); ?></h3>
                    <p><?php echo getTranslation('register_divorce'); ?></p>
                </div>
            </div>

            <div class="service-card" data-service="death" data-aos="fade-up" data-aos-delay="400" style="background-image: url('assets/images/death/death.avif');">
                <div class="service-content">
                    <div class="service-icon">
                        <i class="bi bi-tree-fill"></i>
                    </div>
                    <h3><?php echo getTranslation('death_registration'); ?></h3>
                    <p><?php echo getTranslation('register_death'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- AUTOMATIC SLIDESHOW SECTION (10 IMAGES) -->
<!-- ============================================ -->
<section class="slideshow-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2><?php echo getTranslation('gallery'); ?></h2>
            <p><?php echo getTranslation('gallery_subtitle'); ?></p>
        </div>

        <div class="slideshow-container" data-aos="fade-up">
            <!-- Slide 1 -->
            <div class="slide active">
                <img src="assets/images/slideshow/slide1.jpeg" alt="Birth Registration">
                <div class="slide-caption">
                    <h3><?php echo getTranslation('birth_registration'); ?></h3>
                    <p>Celebrating new beginnings / አዲስ ሕይወት ማክበር</p>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="slide">
                <img src="assets/images/slideshow/slide2.webp" alt="Marriage Registration">
                <div class="slide-caption">
                    <h3><?php echo getTranslation('marriage_registration'); ?></h3>
                    <p>Uniting hearts and families / ልቦችን እና ቤተሰቦችን ማሰራገት</p>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="slide">
                <img src="assets/images/slideshow/slide3.jpeg" alt="Digital Services">
                <div class="slide-caption">
                    <h3>Digital Services</h3>
                    <p>Modern and efficient registration / ዘመናዊ እና ቀልጣፋ ምዝገባ</p>
                </div>
            </div>

            <!-- Slide 4 -->
            <div class="slide">
                <img src="assets/images/slideshow/slide4.webp" alt="Certificate Issuance">
                <div class="slide-caption">
                    <h3>Certificate Issuance</h3>
                    <p>Official and secure documents / ኦፊሴላዊ እና ደህንነቱ የተጠበቀ ሰነድ</p>
                </div>
            </div>

            <!-- Slide 5 -->
            <div class="slide">
                <img src="assets/images/slideshow/slide5.jpg" alt="Customer Service">
                <div class="slide-caption">
                    <h3>Customer Service</h3>
                    <p>Dedicated support team / ታማኝ የድጋፍ ቡድን</p>
                </div>
            </div>

            <!-- Slide 6 -->
            <div class="slide">
                <img src="assets/images/slideshow/slide6.jpg" alt="Registration Office">
                <div class="slide-caption">
                    <h3>Registration Office</h3>
                    <p>Modern facilities / ዘመናዊ መገልገያዎች</p>
                </div>
            </div>

            <!-- Slide 7 -->
            <div class="slide">
                <img src="assets/images/slideshow/slide7.jpg" alt="Digital Kiosk">
                <div class="slide-caption">
                    <h3>Digital Kiosk</h3>
                    <p>Self-service registration / ራስን ማገልገያ ምዝገባ</p>
                </div>
            </div>

            <!-- Slide 8 -->
            <div class="slide">
                <img src="assets/images/slideshow/slide8.jpeg" alt="Mobile Registration">
                <div class="slide-caption">
                    <h3>Mobile Registration</h3>
                    <p>Registration on the go / በእንቅስቃሴ ላይ ምዝገባ</p>
                </div>
            </div>

            <!-- Slide 9 -->
            <div class="slide">
                <img src="assets/images/slideshow/slide9.webp" alt="Community Outreach">
                <div class="slide-caption">
                    <h3>Community Outreach</h3>
                    <p>Serving the community / ማህበረሰቡን ማገልገል</p>
                </div>
            </div>

            <!-- Slide 10 -->
            <div class="slide">
                <img src="assets/images/slideshow/slide10.jpg" alt="National ID">
                <div class="slide-caption">
                    <h3>National ID Integration</h3>
                    <p>Seamless digital identity / እንከን የለሽ ዲጂታል መታወቂያ</p>
                </div>
            </div>

            <!-- Navigation Arrows -->
            <a class="prev" onclick="changeSlide(-1)">&#10094;</a>
            <a class="next" onclick="changeSlide(1)">&#10095;</a>
        </div>

        <!-- Dots/Indicators -->
        <div class="dots-container">
            <span class="dot" onclick="currentSlide(1)"></span>
            <span class="dot" onclick="currentSlide(2)"></span>
            <span class="dot" onclick="currentSlide(3)"></span>
            <span class="dot" onclick="currentSlide(4)"></span>
            <span class="dot" onclick="currentSlide(5)"></span>
            <span class="dot" onclick="currentSlide(6)"></span>
            <span class="dot" onclick="currentSlide(7)"></span>
            <span class="dot" onclick="currentSlide(8)"></span>
            <span class="dot" onclick="currentSlide(9)"></span>
            <span class="dot" onclick="currentSlide(10)"></span>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">50K+</div>
                <div class="stat-label"><?php echo getTranslation('registered_citizens'); ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number">100+</div>
                <div class="stat-label"><?php echo getTranslation('registration_offices'); ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label"><?php echo getTranslation('online_service'); ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number">100%</div>
                <div class="stat-label"><?php echo getTranslation('digital_integration'); ?></div>
            </div>
        </div>
    </div>
</section>

<!-- <script src="assets/js/main.js" ></script> -->

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 1000,
        once: true
    });

    // ============================================
    // AUTOMATIC SLIDESHOW FUNCTIONALITY (10 Images)
    // ============================================
    let slideIndex = 1;
    let slideInterval;
    let totalSlides = 10;

    function showSlides(n) {
        let slides = document.getElementsByClassName("slide");
        let dots = document.getElementsByClassName("dot");

        if (n > slides.length) slideIndex = 1;
        if (n < 1) slideIndex = slides.length;

        for (let i = 0; i < slides.length; i++) {
            slides[i].classList.remove("active");
        }

        for (let i = 0; i < dots.length; i++) {
            dots[i].classList.remove("active");
        }

        if (slides[slideIndex - 1]) {
            slides[slideIndex - 1].classList.add("active");
        }
        if (dots[slideIndex - 1]) {
            dots[slideIndex - 1].classList.add("active");
        }
    }

    function changeSlide(n) {
        showSlides(slideIndex += n);
        resetInterval();
    }

    function currentSlide(n) {
        showSlides(slideIndex = n);
        resetInterval();
    }

    function autoSlide() {
        showSlides(slideIndex += 1);
    }

    function startInterval() {
        slideInterval = setInterval(autoSlide, 5000);
    }

    function resetInterval() {
        clearInterval(slideInterval);
        startInterval();
    }

    // Start automatic slideshow
    startInterval();

    // Service card click handlers
    document.querySelectorAll('.service-card').forEach(card => {
        card.addEventListener('click', function() {
            window.location.href = 'login_simple.php';
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
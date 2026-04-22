/* ============================================ */
// GLOBAL VARIABLES
/* ============================================ */

const STORAGE_KEY = "vital_events_language";

let currentLang = localStorage.getItem(STORAGE_KEY) || "en";

/* ============================================ */
// LANGUAGE SWITCHING FUNCTIONALITY
/* ============================================ */

function setLanguage(lang) {
  currentLang = lang;
  localStorage.setItem(STORAGE_KEY, lang);
  location.reload();
}

function initLanguageSwitcher() {
  const langBtns = document.querySelectorAll(".lang-btn");
  langBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const lang = btn.getAttribute("data-lang");
      setLanguage(lang);
    });
    if (btn.getAttribute("data-lang") === currentLang) {
      btn.classList.add("active");
    }
  });
}

/* ============================================ */
// SCROLL ANIMATIONS
/* ============================================ */

function initScrollAnimations() {
  const elements = document.querySelectorAll(
    ".service-card, .stat-card, .certificate-card, .hero-login-card",
  );

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("animate");
        }
      });
    },
    { threshold: 0.1 },
  );

  elements.forEach((el) => observer.observe(el));
}

/* ============================================ */
// OTP FUNCTIONALITY
/* ============================================ */

let otpInputs = [];

function buildOtpFields(containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;

  container.innerHTML = "";
  otpInputs = [];

  for (let i = 0; i < 6; i++) {
    const input = document.createElement("input");
    input.type = "text";
    input.maxLength = 1;
    input.classList.add("otp-input", "text-center", "mx-1");
    input.setAttribute("data-index", i);

    input.addEventListener("input", (e) => {
      const value = e.target.value;
      if (value && /^[0-9]$/.test(value) && i < 5) {
        otpInputs[i + 1]?.focus();
      } else if (value && !/^[0-9]$/.test(value)) {
        e.target.value = "";
      }
    });

    input.addEventListener("keydown", (e) => {
      if (e.key === "Backspace" && !e.target.value && i > 0) {
        otpInputs[i - 1]?.focus();
      }
    });

    container.appendChild(input);
    otpInputs.push(input);
  }
}

function showMessage(elementId, message, type) {
  const msgDiv = document.getElementById(elementId);
  if (msgDiv) {
    msgDiv.innerHTML = message;
    msgDiv.className = `message ${type}`;
    setTimeout(() => {
      msgDiv.innerHTML = "";
      msgDiv.className = "message";
    }, 5000);
  }
}

function initOTP() {
  const sendOtpBtn = document.getElementById("sendOtpBtn");
  const verifyOtpBtn = document.getElementById("verifyOtpBtn");
  const loginContactInput = document.getElementById("loginContactInput");

  if (!sendOtpBtn || !verifyOtpBtn) return;

  sendOtpBtn.addEventListener("click", async function () {
    const contact = loginContactInput?.value.trim();

    if (!contact) {
      showMessage("loginInfoMsg", "Please enter phone or email", "error");
      return;
    }

    sendOtpBtn.disabled = true;
    sendOtpBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

    try {
      const response = await fetch("process/send_otp.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "contact=" + encodeURIComponent(contact),
      });
      const data = await response.json();

      if (data.success) {
        showMessage("loginInfoMsg", data.message, "success");
        document.getElementById("otpSection").style.display = "block";
        if (data.demo_otp) {
          alert(`Demo OTP: ${data.demo_otp}`);
        }
        if (otpInputs[0]) otpInputs[0].focus();
      } else {
        showMessage("loginInfoMsg", data.message, "error");
      }
    } catch (error) {
      showMessage("loginInfoMsg", "Network error. Please try again.", "error");
    } finally {
      sendOtpBtn.disabled = false;
      sendOtpBtn.innerHTML = "Send OTP";
    }
  });

  verifyOtpBtn.addEventListener("click", async function () {
    const enteredOtp = otpInputs.map((inp) => inp.value).join("");

    if (enteredOtp.length !== 6) {
      showMessage("loginInfoMsg", "Please enter 6-digit code", "error");
      return;
    }

    verifyOtpBtn.disabled = true;
    verifyOtpBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';

    try {
      const response = await fetch("process/verify_otp.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "otp_code=" + encodeURIComponent(enteredOtp),
      });
      const data = await response.json();

      if (data.success) {
        showMessage("loginInfoMsg", data.message, "success");
        setTimeout(() => {
          window.location.href = "dashboard.php";
        }, 1500);
      } else {
        showMessage("loginInfoMsg", data.message, "error");
        otpInputs.forEach((inp) => (inp.value = ""));
        if (otpInputs[0]) otpInputs[0].focus();
      }
    } catch (error) {
      showMessage("loginInfoMsg", "Network error. Please try again.", "error");
    } finally {
      verifyOtpBtn.disabled = false;
      verifyOtpBtn.innerHTML = "Verify & Sign In";
    }
  });
}

/* ============================================ */
// MODAL HANDLING
/* ============================================ */

function fixModalBackdrop() {
  const backdrops = document.querySelectorAll(".modal-backdrop");
  backdrops.forEach((backdrop) => backdrop.remove());
  document.body.classList.remove("modal-open");
  document.body.style.overflow = "";
  document.body.style.paddingRight = "";
}

function initModals() {
  const modals = document.querySelectorAll(".modal");

  modals.forEach((modal) => {
    modal.addEventListener("hidden.bs.modal", function () {
      fixModalBackdrop();
      const form = modal.querySelector("form");
      if (form) form.reset();
      const messages = modal.querySelectorAll(".message");
      messages.forEach((msg) => {
        msg.innerHTML = "";
        msg.className = "message";
      });
    });
  });
}

/* ============================================ */
// DASHBOARD SERVICE CARDS
/* ============================================ */

function initServiceCards() {
  const serviceCards = document.querySelectorAll(".service-card");
  const navLinks = document.querySelectorAll("[data-service]");

  function openModal(service) {
    const modalId = service + "Modal";
    const modal = document.getElementById(modalId);
    if (modal) {
      const bsModal = new bootstrap.Modal(modal);
      bsModal.show();
    }
  }

  serviceCards.forEach((card) => {
    card.addEventListener("click", () => {
      const service = card.getAttribute("data-service");
      if (service) openModal(service);
    });
  });

  navLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const service = link.getAttribute("data-service");
      if (service) openModal(service);
    });
  });
}

/* ============================================ */
// SLIDESHOW FUNCTIONALITY
/* ============================================ */

function initSlideshow() {
  let slideIndex = 0;
  const slides = document.querySelectorAll(".slide");
  const dots = document.querySelectorAll(".dot");

  if (slides.length === 0) return;

  function showSlides() {
    for (let i = 0; i < slides.length; i++) {
      slides[i].classList.remove("active");
    }
    slideIndex++;
    if (slideIndex > slides.length) slideIndex = 1;
    if (slides[slideIndex - 1]) {
      slides[slideIndex - 1].classList.add("active");
    }
    if (dots.length > 0) {
      for (let i = 0; i < dots.length; i++) {
        dots[i].classList.remove("active");
      }
      if (dots[slideIndex - 1]) {
        dots[slideIndex - 1].classList.add("active");
      }
    }
    setTimeout(showSlides, 5000);
  }

  showSlides();
}
AOS.init({ duration: 1000, once: true });

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
  showSlides((slideIndex += n));
  resetInterval();
}

function currentSlide(n) {
  showSlides((slideIndex = n));
  resetInterval();
}

function autoSlide() {
  showSlides((slideIndex += 1));
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
document.querySelectorAll(".service-card").forEach((card) => {
  card.addEventListener("click", function () {
    window.location.href = "login_simple.php";
  });
});


   

/* ============================================ */
// INITIALIZE ALL FUNCTIONS
/* ============================================ */

document.addEventListener("DOMContentLoaded", function () {
  initLanguageSwitcher();
  initScrollAnimations();
  initModals();
  initServiceCards();
  initSlideshow();

  if (document.getElementById("otpContainer")) {
    buildOtpFields("otpContainer");
    initOTP();
  }
});

window.addEventListener("load", function () {
  fixModalBackdrop();
});

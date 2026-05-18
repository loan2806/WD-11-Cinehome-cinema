document.addEventListener("DOMContentLoaded", function () {
    const slides = document.querySelectorAll(".hero-slide");
    let currentSlide = 0;

    function showSlide(index) {
        slides.forEach(function (slide) {
            slide.classList.remove("active");
        });

        slides[index].classList.add("active");
    }

    if (slides.length > 0) {
        showSlide(currentSlide);

        setInterval(function () {
            currentSlide++;

            if (currentSlide >= slides.length) {
                currentSlide = 0;
            }

            showSlide(currentSlide);
        }, 5000);
    }

    const navbar = document.querySelector(".cine-navbar");

    window.addEventListener("scroll", function () {
        if (window.scrollY > 80) {
            navbar.classList.add("scrolled");
        } else {
            navbar.classList.remove("scrolled");
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const dropdownBtn = document.getElementById("userDropdownBtn");
    const dropdownMenu = document.getElementById("userDropdownMenu");
    const dropdownBox = document.getElementById("userDropdownBox");

    if (dropdownBtn && dropdownMenu && dropdownBox) {
        dropdownBtn.addEventListener("click", function (event) {
            event.stopPropagation();
            dropdownMenu.classList.toggle("hidden");
        });

        document.addEventListener("click", function (event) {
            if (!dropdownBox.contains(event.target)) {
                dropdownMenu.classList.add("hidden");
            }
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const authModal = document.getElementById("authModal");
    const authModalOverlay = document.getElementById("authModalOverlay");
    const closeAuthModal = document.getElementById("closeAuthModal");

    const openButtons = document.querySelectorAll("[data-auth-open]");
    const tabButtons = document.querySelectorAll("[data-auth-tab]");
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");

    function openModal(tab = "login") {
    if (!authModal) return;

    authModal.classList.remove("hidden");
    authModal.classList.remove("auth-modal-leave");
    authModal.classList.add("flex");

    switchTab(tab);
    document.body.classList.add("overflow-hidden");
}

    function closeModal() {
    if (!authModal) return;

    authModal.classList.add("auth-modal-leave");

    setTimeout(function () {
        authModal.classList.add("hidden");
        authModal.classList.remove("flex");
        authModal.classList.remove("auth-modal-leave");

        document.body.classList.remove("overflow-hidden");
    }, 220);
}

    function switchTab(tab) {
        if (!loginForm || !registerForm) return;

        if (tab === "register") {
        loginForm.classList.add("hidden");

        registerForm.classList.remove("hidden");
        registerForm.classList.remove("auth-form-animate");

        void registerForm.offsetWidth;

        registerForm.classList.add("auth-form-animate");
    } else {
        registerForm.classList.add("hidden");

        loginForm.classList.remove("hidden");
        loginForm.classList.remove("auth-form-animate");

        void loginForm.offsetWidth;

        loginForm.classList.add("auth-form-animate");
    }

        tabButtons.forEach(function (button) {
            const buttonTab = button.getAttribute("data-auth-tab");

            if (buttonTab === tab) {
                button.classList.add("bg-[#d99a32]", "text-[#2b1208]");
                button.classList.remove("text-gray-300");
            } else {
                button.classList.remove("bg-[#d99a32]", "text-[#2b1208]");
                button.classList.add("text-gray-300");
            }
        });
    }

    openButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const tab = button.getAttribute("data-auth-open") || "login";
            openModal(tab);
        });
    });

    tabButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const tab = button.getAttribute("data-auth-tab") || "login";
            switchTab(tab);
        });
    });

    if (closeAuthModal) {
        closeAuthModal.addEventListener("click", closeModal);
    }

    if (authModalOverlay) {
        authModalOverlay.addEventListener("click", closeModal);
    }

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeModal();
        }
    });

    const togglePasswordButtons = document.querySelectorAll("[data-toggle-password]");

    togglePasswordButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const inputId = button.getAttribute("data-toggle-password");
            const input = document.getElementById(inputId);

            if (!input) return;

            input.type = input.type === "password" ? "text" : "password";

            const icon = button.querySelector("i");

            if (icon) {
                icon.classList.toggle("fa-eye");
                icon.classList.toggle("fa-eye-slash");
            }
        });
    });
});
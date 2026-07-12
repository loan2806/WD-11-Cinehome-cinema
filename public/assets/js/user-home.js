document.addEventListener("DOMContentLoaded", function () {
    const navbar = document.querySelector(".cine-navbar");

    if (navbar) {
        window.addEventListener("scroll", function () {
            navbar.classList.toggle("scrolled", window.scrollY > 80);
        });
    }

    const slider = document.querySelector("[data-home-slider]");

    if (slider) {
        const slides = slider.querySelectorAll(".hero-slide");
        const dots = slider.querySelectorAll("[data-slide-target]");
        const prevButton = slider.querySelector("[data-slide-prev]");
        const nextButton = slider.querySelector("[data-slide-next]");
        let currentSlide = 0;
        let autoplayTimer = null;

        function showSlide(index) {
            if (!slides.length) return;

            currentSlide = (index + slides.length) % slides.length;

            slides.forEach(function (slide, slideIndex) {
                slide.classList.toggle("active", slideIndex === currentSlide);
            });

            dots.forEach(function (dot, dotIndex) {
                dot.classList.toggle("active", dotIndex === currentSlide);

                const progress = dot.querySelector("span");

                if (progress) {
                    progress.style.transition = "none";
                    progress.style.width = "0";

                    if (dotIndex === currentSlide) {
                        requestAnimationFrame(function () {
                            progress.style.transition = "";
                            progress.style.width = "100%";
                        });
                    }
                }
            });
        }

        function nextSlide() {
            showSlide(currentSlide + 1);
        }

        function startAutoplay() {
            stopAutoplay();
            autoplayTimer = setInterval(nextSlide, 5200);
        }

        function stopAutoplay() {
            if (autoplayTimer) {
                clearInterval(autoplayTimer);
                autoplayTimer = null;
            }
        }

        dots.forEach(function (dot) {
            dot.addEventListener("click", function () {
                const index = Number(dot.getAttribute("data-slide-target"));
                showSlide(index);
                startAutoplay();
            });
        });

        if (prevButton) {
            prevButton.addEventListener("click", function () {
                showSlide(currentSlide - 1);
                startAutoplay();
            });
        }

        if (nextButton) {
            nextButton.addEventListener("click", function () {
                showSlide(currentSlide + 1);
                startAutoplay();
            });
        }

        slider.addEventListener("mouseenter", stopAutoplay);
        slider.addEventListener("mouseleave", startAutoplay);

        showSlide(0);

        if (slides.length > 1) {
            startAutoplay();
        }
    } else {
        const slides = document.querySelectorAll(".hero-slide");
        let currentSlide = 0;

        function showSlide(index) {
            slides.forEach(function (slide) {
                slide.classList.remove("active");
            });

            if (slides[index]) {
                slides[index].classList.add("active");
            }
        }

        if (slides.length > 0) {
            showSlide(currentSlide);

            setInterval(function () {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            }, 5000);
        }
    }

    const revealItems = document.querySelectorAll(".reveal-on-scroll");
    const railSections = document.querySelectorAll("[data-rail-section]");

    railSections.forEach(function (section) {
        const rail = section.querySelector(".booking-movie-rail");
        const prevButton = section.querySelector("[data-rail-prev]");
        const nextButton = section.querySelector("[data-rail-next]");

        if (!rail) return;

        function scrollRail(direction) {
            const firstCard = rail.querySelector(".booking-movie-card");
            const cardWidth = firstCard ? firstCard.getBoundingClientRect().width : 220;
            const gap = 18;
            const scrollAmount = Math.max(cardWidth + gap, rail.clientWidth * 0.72);

            rail.scrollBy({
                left: direction * scrollAmount,
                behavior: "smooth",
            });
        }

        if (prevButton) {
            prevButton.addEventListener("click", function () {
                scrollRail(-1);
            });
        }

        if (nextButton) {
            nextButton.addEventListener("click", function () {
                scrollRail(1);
            });
        }
    });

    const detailTabShells = document.querySelectorAll("[data-detail-tabs]");

    detailTabShells.forEach(function (shell) {
        const buttons = shell.querySelectorAll("[data-detail-tab]");
        const panels = shell.querySelectorAll("[data-detail-panel]");

        function activateTab(tabName) {
            buttons.forEach(function (button) {
                const isActive = button.getAttribute("data-detail-tab") === tabName;
                button.classList.toggle("active", isActive);
                button.setAttribute("aria-selected", isActive ? "true" : "false");
            });

            panels.forEach(function (panel) {
                const isActive = panel.getAttribute("data-detail-panel") === tabName;
                panel.classList.toggle("active", isActive);
            });
        }

        buttons.forEach(function (button) {
            button.addEventListener("click", function () {
                activateTab(button.getAttribute("data-detail-tab") || "overview");
            });
        });

        activateTab("overview");
    });

    if ("IntersectionObserver" in window && revealItems.length) {
        const revealObserver = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("is-visible");
                        revealObserver.unobserve(entry.target);
                    }
                });
            },
            {
                threshold: 0.16,
                rootMargin: "0px 0px -40px 0px",
            }
        );

        revealItems.forEach(function (item) {
            revealObserver.observe(item);
        });
    } else {
        revealItems.forEach(function (item) {
            item.classList.add("is-visible");
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const dropdownBtn = document.getElementById("userDropdownBtn");
    const dropdownMenu = document.getElementById("userDropdownMenu");
    const dropdownBox = document.getElementById("userDropdownBox");

    if (dropdownBtn && dropdownMenu && dropdownBox) {
        function openUserDropdown() {
            dropdownMenu.hidden = false;
            dropdownMenu.classList.remove("hidden");
            dropdownBox.classList.add("is-open");
            dropdownBtn.setAttribute("aria-expanded", "true");
        }

        function closeUserDropdown() {
            dropdownMenu.hidden = true;
            dropdownMenu.classList.add("hidden");
            dropdownBox.classList.remove("is-open");
            dropdownBtn.setAttribute("aria-expanded", "false");
        }

        closeUserDropdown();

        dropdownBtn.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (dropdownMenu.hidden || dropdownMenu.classList.contains("hidden")) {
                openUserDropdown();
            } else {
                closeUserDropdown();
            }
        });

        dropdownMenu.addEventListener("click", function (event) {
            event.stopPropagation();
        });

        document.addEventListener("click", function (event) {
            if (!dropdownBox.contains(event.target)) {
                closeUserDropdown();
            }
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                closeUserDropdown();
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
    const authModalSubtitle = document.getElementById("authModalSubtitle");

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
            const isActive = buttonTab === tab;

            button.classList.toggle("is-active", isActive);
            button.setAttribute("aria-selected", isActive ? "true" : "false");
        });

        if (authModalSubtitle) {
            authModalSubtitle.textContent = tab === "register"
                ? "Tạo tài khoản để nhận ưu đãi thành viên"
                : "Đăng nhập để đặt vé và quản lý vé của bạn";
        }
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

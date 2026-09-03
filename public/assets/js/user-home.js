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
        
        const SLIDE_DURATION = 7000; // 7 giây theo yêu cầu của người dùng
        let currentSlide = 0;
        let autoplayTimer = null;
        let isHovered = false;

        function showSlide(index) {
            if (!slides.length) return;

            currentSlide = (index + slides.length) % slides.length;

            slides.forEach(function (slide, slideIndex) {
                slide.classList.toggle("active", slideIndex === currentSlide);
            });

            dots.forEach(function (dot, dotIndex) {
                const isActive = dotIndex === currentSlide;
                dot.classList.toggle("active", isActive);

                const progress = dot.querySelector("span");
                if (progress) {
                    progress.style.transition = "none";
                    progress.style.width = "0%";

                    if (isActive && !isHovered) {
                        requestAnimationFrame(function () {
                            progress.style.transition = `width ${SLIDE_DURATION}ms linear`;
                            progress.style.width = "100%";
                        });
                    }
                }
            });
        }

        function nextSlide() {
            if (isHovered) return;
            showSlide(currentSlide + 1);
        }

        function startAutoplay() {
            stopAutoplay();
            if (slides.length <= 1 || isHovered) return;

            const activeDot = slider.querySelector("[data-slide-target].active span");
            if (activeDot) {
                activeDot.style.transition = `width ${SLIDE_DURATION}ms linear`;
                activeDot.style.width = "100%";
            }

            autoplayTimer = setInterval(nextSlide, SLIDE_DURATION);
        }

        function stopAutoplay() {
            if (autoplayTimer) {
                clearInterval(autoplayTimer);
                autoplayTimer = null;
            }

            const activeDot = slider.querySelector("[data-slide-target].active span");
            if (activeDot) {
                const computedWidth = window.getComputedStyle(activeDot).width;
                activeDot.style.transition = "none";
                activeDot.style.width = computedWidth;
            }
        }

        dots.forEach(function (dot) {
            dot.addEventListener("click", function () {
                const index = Number(dot.getAttribute("data-slide-target"));
                showSlide(index);
                if (!isHovered) startAutoplay();
            });
        });

        if (prevButton) {
            prevButton.addEventListener("click", function () {
                showSlide(currentSlide - 1);
                if (!isHovered) startAutoplay();
            });
        }

        if (nextButton) {
            nextButton.addEventListener("click", function () {
                showSlide(currentSlide + 1);
                if (!isHovered) startAutoplay();
            });
        }

        slider.addEventListener("mouseenter", function () {
            isHovered = true;
            stopAutoplay();
        });

        slider.addEventListener("mouseleave", function () {
            isHovered = false;
            startAutoplay();
        });

        slider.addEventListener("touchstart", function () {
            isHovered = true;
            stopAutoplay();
        }, { passive: true });

        slider.addEventListener("touchend", function () {
            setTimeout(function() {
                isHovered = false;
                startAutoplay();
            }, 3000);
        }, { passive: true });

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
            const gap = 26;
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

/* Cine Custom Select Dropdown Handler */
document.addEventListener("DOMContentLoaded", function () {
    const customSelects = document.querySelectorAll(".cine-custom-select");

    if (!customSelects.length) return;

    customSelects.forEach(function (selectContainer) {
        const trigger = selectContainer.querySelector(".cine-select-trigger");
        const dropdown = selectContainer.querySelector(".cine-select-dropdown");
        const hiddenInput = selectContainer.querySelector("input[type='hidden']");
        const valueSpan = selectContainer.querySelector(".cine-select-value");
        const options = selectContainer.querySelectorAll(".cine-option");

        if (!trigger || !dropdown) return;

        trigger.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const isOpen = selectContainer.classList.contains("is-open");

            // Close all other custom selects
            customSelects.forEach(function (other) {
                if (other !== selectContainer) {
                    other.classList.remove("is-open");
                }
            });

            selectContainer.classList.toggle("is-open", !isOpen);
        });

        options.forEach(function (option) {
            option.addEventListener("click", function (e) {
                e.stopPropagation();
                const val = option.getAttribute("data-value") || "";
                const text = option.querySelector("span") ? option.querySelector("span").textContent : "";

                if (hiddenInput) hiddenInput.value = val;
                if (valueSpan) valueSpan.textContent = text;

                options.forEach(function (o) {
                    o.classList.remove("selected");
                });
                option.classList.add("selected");

                selectContainer.classList.remove("is-open");

                // Submit parent filter form automatically
                const form = selectContainer.closest("form");
                if (form) {
                    form.submit();
                }
            });
        });
    });

    // Close open dropdowns when clicking outside
    document.addEventListener("click", function (e) {
        customSelects.forEach(function (selectContainer) {
            if (!selectContainer.contains(e.target)) {
                selectContainer.classList.remove("is-open");
            }
        });
    });

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
            customSelects.forEach(function (selectContainer) {
                selectContainer.classList.remove("is-open");
            });
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const sidebarToggle = document.getElementById("sidebarToggle");
    const adminLayout = document.getElementById("adminLayout");

    const adminDropdownBtn = document.getElementById("adminDropdownBtn");
    const adminDropdownMenu = document.getElementById("adminDropdownMenu");
    const adminDropdownBox = document.getElementById("adminDropdownBox");

    // Toggle sidebar bằng data attribute, không dùng class Tailwind động
    if (sidebarToggle && adminLayout) {
        sidebarToggle.addEventListener("click", function () {
            const currentState = adminLayout.dataset.sidebar;

            adminLayout.dataset.sidebar = currentState === "open" ? "closed" : "open";
        });
    }

    // Toggle admin dropdown
    if (adminDropdownBtn && adminDropdownMenu && adminDropdownBox) {
        adminDropdownBtn.addEventListener("click", function (event) {
            event.stopPropagation();
            adminDropdownMenu.classList.toggle("hidden");
        });

        document.addEventListener("click", function (event) {
            if (!adminDropdownBox.contains(event.target)) {
                adminDropdownMenu.classList.add("hidden");
            }
        });
    }
});
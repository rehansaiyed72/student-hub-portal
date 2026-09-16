// ==========================================
// STUDENT HUB - LIGHT / DARK MODE
// ==========================================

document.addEventListener("DOMContentLoaded", function () {

    const themeToggle = document.getElementById("themeToggle");

    // Make sure the button exists
    if (!themeToggle) {
        console.error("Theme button with id='themeToggle' was not found.");
        return;
    }

    // Get saved theme
    const savedTheme = localStorage.getItem("studentHubTheme");

    // Apply saved theme
    if (savedTheme === "light") {

        document.body.classList.remove("dark-mode");
        document.body.classList.add("light-mode");

        themeToggle.textContent = "🌙 Dark Mode";

    } else {

        document.body.classList.remove("light-mode");
        document.body.classList.add("dark-mode");

        themeToggle.textContent = "☀️ Light Mode";
    }


    // ==========================================
    // TOGGLE THEME
    // ==========================================

    themeToggle.addEventListener("click", function () {

        if (document.body.classList.contains("dark-mode")) {

            // Switch to light
            document.body.classList.remove("dark-mode");
            document.body.classList.add("light-mode");

            themeToggle.textContent = "🌙 Dark Mode";

            localStorage.setItem("studentHubTheme", "light");

        } else {

            // Switch to dark
            document.body.classList.remove("light-mode");
            document.body.classList.add("dark-mode");

            themeToggle.textContent = "☀️ Light Mode";

            localStorage.setItem("studentHubTheme", "dark");
        }

    });

});
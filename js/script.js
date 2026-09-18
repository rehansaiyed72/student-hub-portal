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

document.getElementById("registrationForm").addEventListener("submit", function(event) {

    event.preventDefault();

    // Get values
    let name = document.getElementById("name").value.trim();
    let email = document.getElementById("email").value.trim();
    let mobile = document.getElementById("mobile").value.trim();
    let password = document.getElementById("password").value;
    let confirmPassword = document.getElementById("confirmPassword").value;
    let course = document.getElementById("course").value;
    let year = document.getElementById("year").value;
    let terms = document.getElementById("terms").checked;
    let gender = document.querySelector('input[name="gender"]:checked');

    // Regular Expressions
    let nameRegex = /^[A-Za-z ]{2,}$/;

    let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    let mobileRegex = /^[6-9][0-9]{9}$/;

    let passwordRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[@#$%^&*!]).{8,}$/;

    // Clear previous messages
    document.querySelectorAll("span").forEach(function(span) {
        span.innerHTML = "";
    });

    document.getElementById("success").innerHTML = "";

    let valid = true;

    // Name validation
    if (!nameRegex.test(name)) {
        document.getElementById("nameError").innerHTML =
            " Invalid name";
        valid = false;
    }

    // Email validation
    if (!emailRegex.test(email)) {
        document.getElementById("emailError").innerHTML =
            " Invalid email";
        valid = false;
    }

    // Mobile validation
    if (!mobileRegex.test(mobile)) {
        document.getElementById("mobileError").innerHTML =
            " Invalid mobile number";
        valid = false;
    }

    // Password validation
    if (!passwordRegex.test(password)) {
        document.getElementById("passwordError").innerHTML =
            " Password must contain 8+ characters, uppercase, lowercase, number and special character";
        valid = false;
    }

    // Confirm password
    if (password !== confirmPassword) {
        document.getElementById("confirmPasswordError").innerHTML =
            " Passwords do not match";
        valid = false;
    }

    // Course
    if (course === "") {
        document.getElementById("courseError").innerHTML =
            " Select a course";
        valid = false;
    }

    // Year
    if (year === "") {
        document.getElementById("yearError").innerHTML =
            " Select your year";
        valid = false;
    }

    // Gender
    if (!gender) {
        document.getElementById("genderError").innerHTML =
            " Select gender";
        valid = false;
    }

    // Terms
    if (!terms) {
        document.getElementById("termsError").innerHTML =
            " Accept the terms";
        valid = false;
    }

    // Final result
    if (valid) {
        document.getElementById("success").innerHTML =
            "Registration Successful!";

        document.getElementById("registrationForm").reset();
    }

});
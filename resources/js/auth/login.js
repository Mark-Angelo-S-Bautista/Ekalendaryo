/**
 * Toggles the visibility of the password input field
 * and changes the eye icon to match the current state.
 */
function togglePasswordVisibility() {
    // 1. Get the password input field and the toggle icon
    const passwordInput = document.getElementById("password");
    const toggleIcon = document.getElementById("password-toggle");

    // 2. Check the current type of the input
    if (passwordInput.type === "password") {
        // If it's a password type, change it to text (show the characters)
        passwordInput.type = "text";

        // Change the icon from 'eye' to 'slashed eye' to indicate it's now visible
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
    } else {
        // If it's a text type, change it back to password (hide the characters with dots)
        passwordInput.type = "password";

        // Change the icon back to 'eye' to indicate it's now hidden
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
    }
}

// We attach the function to the click event when the window loads
window.onload = function () {
    const toggleElement = document.getElementById("password-toggle");
    if (toggleElement) {
        // This line ensures that when the user clicks the eye icon, the function above runs.
        toggleElement.addEventListener("click", togglePasswordVisibility);
    }
};

// Toast pop up for the change password success message
document.addEventListener("DOMContentLoaded", function () {
    const toast = document.getElementById("toast");
    if (toast) {
        setTimeout(() => {
            toast.classList.remove("show");
        }, 4000); // disappears after 4 seconds
    }
});

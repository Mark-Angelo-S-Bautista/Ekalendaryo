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

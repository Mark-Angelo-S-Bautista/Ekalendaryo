// ==============================
// Profile JS - Viewer
// ==============================

document.addEventListener("DOMContentLoaded", () => {
    // ==============================
    // Personal Info Edit Toggle
    // ==============================
    const editBtn = document.getElementById("editProfile");
    const saveBtn = document.getElementById("saveProfile");
    const cancelEditBtn = document.getElementById("cancelEdit");
    const adminName = document.getElementById("adminName");
    const userIdField = document.getElementById("UserId");
    const editActions = document.getElementById("editActions");

    if (
        editBtn &&
        saveBtn &&
        cancelEditBtn &&
        adminName &&
        userIdField &&
        editActions
    ) {
        editBtn.addEventListener("click", () => {
            adminName.disabled = false;
            userIdField.disabled = false;
            editActions.classList.remove("hidden");
            editBtn.classList.add("hidden");
            saveBtn.classList.remove("hidden");
        });

        cancelEditBtn.addEventListener("click", () => {
            adminName.disabled = true;
            userIdField.disabled = true;
            editActions.classList.add("hidden");
            editBtn.classList.remove("hidden");
            saveBtn.classList.add("hidden");
        });
    }

    // ==============================
    // Email Form Toggle & AJAX
    // ==============================
    const btnChangeEmail = document.getElementById("btnChangeEmail");
    const emailForm = document.getElementById("emailForm");
    const cancelEmail = document.getElementById("cancelEmail");
    const newEmailError = document.getElementById("newEmailError");
    const emailPasswordError = document.getElementById("emailPasswordError");

    if (btnChangeEmail && emailForm && cancelEmail) {
        // Show email form
        btnChangeEmail.addEventListener("click", () =>
            emailForm.classList.remove("hidden"),
        );

        // Cancel email edit
        cancelEmail.addEventListener("click", () => {
            emailForm.classList.add("hidden");
            emailForm.reset();
            newEmailError.innerText = "";
            emailPasswordError.innerText = "";
        });

        // AJAX submit
        emailForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            console.log("Form submitted"); // Add this
            console.log("Action:", emailForm.action); // Add this

            newEmailError.innerText = "";
            emailPasswordError.innerText = "";

            const formData = new FormData(emailForm);
            console.log("Form data:", Object.fromEntries(formData)); // Add this

            try {
                const response = await fetch(emailForm.action, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content,
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest", // Add this header
                    },
                    body: formData,
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.errors?.new_email)
                        newEmailError.innerText = data.errors.new_email[0];
                    if (data.errors?.current_password)
                        emailPasswordError.innerText =
                            data.errors.current_password[0];
                    return;
                }

                showToast(data.message);
                emailForm.reset();
                emailForm.classList.add("hidden");

                // Update the displayed email (optional)
                const emailDisplay = document.querySelector(".muted");
                if (emailDisplay) {
                    emailDisplay.textContent =
                        "Verification email sent. Please check your inbox.";
                }
            } catch (err) {
                console.error("Error:", err); // Add error logging
                showToast("Something went wrong.");
            }
        });
    }

    // ==============================
    // Password Form Toggle & AJAX
    // ==============================
    const btnChangePassword = document.getElementById("btnChangePassword");
    const passwordForm = document.getElementById("passwordForm");
    const cancelPassword = document.getElementById("cancelPassword");
    const currentPasswordError = document.getElementById(
        "currentPasswordError",
    );
    const confirmPasswordError = document.getElementById(
        "confirmPasswordError",
    );

    if (btnChangePassword && passwordForm && cancelPassword) {
        // Show password form
        btnChangePassword.addEventListener("click", () =>
            passwordForm.classList.remove("hidden"),
        );

        // Cancel password edit
        cancelPassword.addEventListener("click", () => {
            passwordForm.reset();
            passwordForm.classList.add("hidden");
            clearPasswordErrors();
        });

        // AJAX submit
        passwordForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            clearPasswordErrors();

            const formData = new FormData(passwordForm);

            try {
                const response = await fetch(passwordForm.action, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content,
                        Accept: "application/json",
                    },
                    body: formData,
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.errors?.current_password)
                        currentPasswordError.innerText =
                            data.errors.current_password[0];
                    if (data.errors?.new_password)
                        confirmPasswordError.innerText =
                            data.errors.new_password[0];
                    return;
                }

                // Success
                showToast(data.message);
                passwordForm.reset();
                passwordForm.classList.add("hidden");
            } catch (err) {
                currentPasswordError.innerText =
                    "Something went wrong. Please try again.";
            }
        });

        function clearPasswordErrors() {
            currentPasswordError.innerText = "";
            confirmPasswordError.innerText = "";
        }
    }

    // ==============================
    // Toggle Password Visibility
    // ==============================
    document.addEventListener("click", (e) => {
        const btn = e.target.closest("[data-toggle-password]");
        if (!btn) return;

        const wrapper = btn.closest(".password-wrapper");
        if (!wrapper) return;

        const input = wrapper.querySelector(
            "input[type='password'], input[type='text']",
        );
        if (!input) return;

        if (input.type === "password") {
            input.type = "text";
            btn.textContent = "👁️‍🗨️";
        } else {
            input.type = "password";
            btn.textContent = "👁";
        }
    });

    // ==============================
    // Toast Notification Function
    // ==============================
    function showToast(message) {
        const toast = document.createElement("div");
        toast.className = "toast";
        toast.innerText = message;
        document.body.appendChild(toast);

        setTimeout(() => toast.classList.add("show"), 100);
        setTimeout(() => {
            toast.classList.remove("show");
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});

// ==============================
// Profile JS
// ==============================

// -- Personal Info Edit Toggle --
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

// -- Email Form Toggle --
const btnChangeEmail = document.getElementById("btnChangeEmail");
const emailForm = document.getElementById("emailForm");
const cancelEmail = document.getElementById("cancelEmail");

if (btnChangeEmail && emailForm && cancelEmail) {
    btnChangeEmail.addEventListener("click", () =>
        emailForm.classList.remove("hidden")
    );
    cancelEmail.addEventListener("click", () =>
        emailForm.classList.add("hidden")
    );
}

// -- Password Form Toggle --
document.addEventListener("DOMContentLoaded", () => {
    const btnChangePassword = document.getElementById("btnChangePassword");
    const passwordForm = document.getElementById("passwordForm");
    const cancelPassword = document.getElementById("cancelPassword");

    const currentPasswordError = document.getElementById(
        "currentPasswordError"
    );
    const confirmPasswordError = document.getElementById(
        "confirmPasswordError"
    );

    if (!passwordForm) return;

    // Show form
    btnChangePassword?.addEventListener("click", () => {
        passwordForm.classList.remove("hidden");
    });

    // Cancel
    cancelPassword?.addEventListener("click", () => {
        passwordForm.reset();
        passwordForm.classList.add("hidden");
        clearErrors();
    });

    // Submit via AJAX
    passwordForm.addEventListener("submit", (e) => {
        e.preventDefault(); // 🚫 NO PAGE RELOAD

        clearErrors();

        const formData = new FormData(passwordForm);

        fetch(passwordForm.action, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
                Accept: "application/json",
            },
            body: formData,
        })
            .then(async (response) => {
                const data = await response.json();

                if (!response.ok) {
                    // Current password error
                    if (data.errors?.current_password) {
                        currentPasswordError.innerText =
                            data.errors.current_password[0];
                    }

                    // Confirm password mismatch
                    if (data.errors?.new_password) {
                        confirmPasswordError.innerText =
                            data.errors.new_password[0];
                    }

                    return;
                }

                // ✅ Success
                showToast(data.message);
                passwordForm.reset();
                passwordForm.classList.add("hidden");
            })
            .catch(() => {
                currentPasswordError.innerText =
                    "Something went wrong. Please try again.";
            });
    });

    function clearErrors() {
        currentPasswordError.innerText = "";
        confirmPasswordError.innerText = "";
    }
});

// Toggle password visibility
function togglePassword(id) {
    const input = document.getElementById(id);
    if (input) {
        input.type = input.type === "password" ? "text" : "password";
    }
}

// -- Toast Notification Function --
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

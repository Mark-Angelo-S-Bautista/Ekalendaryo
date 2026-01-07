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
const btnChangePassword = document.getElementById("btnChangePassword");
const passwordForm = document.getElementById("passwordForm");
const cancelPassword = document.getElementById("cancelPassword");
const passwordErrors = document.getElementById("passwordErrors");

if (btnChangePassword && passwordForm && cancelPassword) {
    btnChangePassword.addEventListener("click", () =>
        passwordForm.classList.remove("hidden")
    );
    cancelPassword.addEventListener("click", () => {
        passwordForm.classList.add("hidden");
        if (passwordErrors) passwordErrors.innerHTML = "";
        passwordForm.reset();
    });
}

// -- Toggle Password Visibility --
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (input) {
        input.type = input.type === "password" ? "text" : "password";
    }
}

const currentPasswordError = document.getElementById("currentPasswordError");

if (passwordForm) {
    passwordForm.addEventListener("submit", function (e) {
        e.preventDefault(); // stop page reload

        // clear previous error
        if (currentPasswordError) {
            currentPasswordError.innerText = "";
        }

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

                // ❌ Validation error (wrong current password)
                if (!response.ok) {
                    if (data.errors && data.errors.current_password) {
                        currentPasswordError.innerText =
                            data.errors.current_password[0];
                        return;
                    }

                    currentPasswordError.innerText = "Password update failed.";
                    return;
                }

                // ✅ Success
                showToast("Password updated successfully");
                passwordForm.reset();
                passwordForm.classList.add("hidden");
            })
            .catch(() => {
                currentPasswordError.innerText =
                    "Something went wrong. Please try again.";
            });
    });
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

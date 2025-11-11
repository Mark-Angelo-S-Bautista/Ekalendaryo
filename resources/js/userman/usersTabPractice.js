// ==========================
// --- ADD DEPARTMENT ---
// ==========================
const addDeptModal = document.getElementById("adddept_overlay");
const addDeptForm = document.getElementById("addDepartmentForm");
const addDeptMessage = document.getElementById("addDeptMessage");
const existingDeptList = document.querySelector(".adddept_list ul");
const addUserDeptSelect = document.getElementById("department"); // Add User modal dropdown

// Utility function to get the department name input field
const addDeptNameInput = addDeptForm?.querySelector(
    'input[name="department_name"]'
);
const addDeptNameError = document.getElementById("error-department_name");

const closeAddDeptModal = () => {
    if (addDeptModal) {
        addDeptModal.style.opacity = "0";
        setTimeout(() => (addDeptModal.style.display = "none"), 200);
        // Clear errors and messages when closing
        if (addDeptMessage) {
            addDeptMessage.textContent = "";
            addDeptMessage.classList.remove("success");
        }
        if (addDeptNameError) addDeptNameError.textContent = "";
        if (addDeptForm) addDeptForm.reset();
    }
};
window.closeAddDeptModal = closeAddDeptModal;

document.getElementById("openAddDept")?.addEventListener("click", () => {
    if (addDeptModal) {
        addDeptModal.style.display = "flex";
        addDeptModal.style.opacity = "0";
        setTimeout(() => (addDeptModal.style.opacity = "1"), 10);
    }
});
document
    .querySelector(".adddept_close")
    ?.addEventListener("click", closeAddDeptModal);
document
    .querySelector(".adddept_btn.cancel")
    ?.addEventListener("click", closeAddDeptModal);
window.addEventListener("click", (e) => {
    if (e.target === addDeptModal) closeAddDeptModal();
});

// --- ADD DEPARTMENT Submission (AJAX) - Corrected Error Handling ---
addDeptForm?.addEventListener("submit", async function (e) {
    e.preventDefault();

    addDeptMessage.textContent = "";
    addDeptMessage.classList.remove("success");
    if (addDeptNameError) addDeptNameError.textContent = ""; // Clear field error

    const formData = new FormData(addDeptForm);

    try {
        const response = await fetch(addDeptForm.action, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": formData.get("_token"),
                Accept: "application/json",
            },
            body: formData,
        });

        const data = await response.json();

        if (data.status === "error") {
            // ❌ Handle Validation Errors for department_name
            if (
                data.errors &&
                data.errors.department_name &&
                addDeptNameError
            ) {
                // Show specific error below the input field
                addDeptNameError.textContent = data.errors.department_name[0];
            }
            // Show general error message
            addDeptMessage.textContent = "Please correct the error above.";
            addDeptMessage.style.color = "red";
        } else {
            // ✅ Success
            addDeptMessage.textContent = data.message;
            addDeptMessage.classList.add("success");
            addDeptMessage.style.color = "green"; // Added color for clarity

            addDeptForm.reset();

            // Add to Existing Departments List
            if (existingDeptList && data.department) {
                const li = document.createElement("li");
                li.innerHTML = `
                    <span>${data.department.department_name}</span>
                    <form action="/UserManagement/deletedepartment/${
                        data.department.id
                    }" method="POST" style="display:inline;" onsubmit="return confirm('Delete ${
                    data.department.department_name
                }?')">
                        <input type="hidden" name="_token" value="${formData.get(
                            "_token"
                        )}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="delete-dept-btn">🗑️</button>
                    </form>
                `;
                existingDeptList.appendChild(li);
            }

            // Add to Add User modal dropdown
            if (addUserDeptSelect && data.department) {
                const option = document.createElement("option");
                option.value = data.department.department_name;
                option.textContent = data.department.department_name;
                addUserDeptSelect.appendChild(option);
            }

            // Optional: Auto close after short delay
            setTimeout(closeAddDeptModal, 1200);
        }
    } catch (err) {
        addDeptMessage.textContent = "Something went wrong!";
        addDeptMessage.classList.remove("success");
        addDeptMessage.style.color = "red"; // Added color for clarity
        console.error(err);
    }
});

// ==========================
// --- ADD USER ---
// ==========================
const addUserOverlay = document.getElementById("adduser_overlay");
const addUserForm = document.querySelector("#adduser_overlay form");
const addUserMessage = document.getElementById("addUserMessage");
const usersTableBody = document.querySelector("#userTable tbody");

const openAddUserModal = () => {
    if (addUserOverlay) {
        addUserOverlay.style.display = "flex";
        addUserOverlay.style.opacity = "0";
        setTimeout(() => (addUserOverlay.style.opacity = "1"), 10);
    }
};
const closeAddUserModal = () => {
    if (addUserOverlay) {
        addUserOverlay.style.opacity = "0";
        setTimeout(() => (addUserOverlay.style.display = "none"), 200);
        if (addUserMessage) {
            addUserMessage.textContent = "";
            addUserMessage.classList.remove("success");
        }
        if (addUserForm) addUserForm.reset();

        // Also reset the form fields visibility when closing
        updateAddUserForm();

        // Clear all field-specific error messages on close
        document
            .querySelectorAll(".error-text")
            .forEach((el) => (el.textContent = ""));
    }
};
window.openAddUserModal = openAddUserModal;
window.closeAddUserModal = closeAddUserModal;
window.addEventListener("click", (e) => {
    if (e.target === addUserOverlay) closeAddUserModal();
});

//Error Handling USER ADD
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("addUserForm");
    const messageDiv = document.getElementById("addUserMessage");

    form.addEventListener("submit", function (e) {
        e.preventDefault(); // Prevent default form submission

        // Clear previous errors
        const errorFields = document.querySelectorAll(".error-text");
        errorFields.forEach((el) => (el.textContent = ""));
        messageDiv.textContent = "";

        // Prepare form data
        const formData = new FormData(form);

        fetch(form.action, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]')
                    .value,
                Accept: "application/json",
            },
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === "error") {
                    const errors = data.errors || {};
                    for (const field in errors) {
                        const errorDiv = document.getElementById(
                            "error-" + field
                        );
                        if (errorDiv) {
                            errorDiv.textContent = errors[field][0]; // show first error per field
                        } else {
                            messageDiv.textContent = errors[field][0]; // fallback general error
                        }
                    }
                } else if (data.status === "success") {
                    // Show success message
                    messageDiv.textContent =
                        data.message || "User added successfully!";
                    messageDiv.style.color = "green";

                    // Reset the form immediately
                    form.reset();

                    // After 3 seconds, close modal and refresh the page
                    setTimeout(() => {
                        closeAddUserModal(); // Close modal
                        location.reload(); // Refresh page
                    }, 2000);
                }
            });
    });
});

// ==========================
// --- Dynamic Add User Form Fields ---
// ==========================
const titleDropdown = addUserForm?.querySelector("#title");
const officeNameField = addUserForm?.querySelector("#office_name_field");
const yearLevelFieldAdd = addUserForm
    ?.querySelector('select[name="yearlevel"]')
    ?.closest(".adduser_form-group");
const sectionFieldAdd = addUserForm
    ?.querySelector('input[name="section"]')
    ?.closest(".adduser_form-group");

const updateAddUserForm = () => {
    // Check if the fields exist before trying to access them
    if (
        !titleDropdown ||
        !officeNameField ||
        !yearLevelFieldAdd ||
        !sectionFieldAdd
    ) {
        // console.error("One or more dynamic form fields are missing from the DOM.");
        return;
    }

    const selectedTitle = titleDropdown.value;

    // --- Logic for Year Level and Section ---
    if (selectedTitle === "Student") {
        yearLevelFieldAdd.style.display = "block";
        sectionFieldAdd.style.display = "block";
    } else {
        yearLevelFieldAdd.style.display = "none";
        sectionFieldAdd.style.display = "none";
    }

    // --- Logic for Office Name ---
    if (selectedTitle === "Offices") {
        officeNameField.style.display = "block";
    } else {
        officeNameField.style.display = "none";
    }
};

// Set the initial state of the form when the page loads
if (addUserForm) {
    updateAddUserForm();
}

// Add the listener ONLY to the title dropdown
if (titleDropdown) {
    titleDropdown.addEventListener("change", updateAddUserForm);
}

// ==========================
// --- EDIT USER ---
// ==========================
document.addEventListener("DOMContentLoaded", () => {
    const editUserForm = document.querySelector(".edituser_wrapper form");
    const editUserMessage = document.getElementById("editUserMessage");
    // Assuming error messages in the edit form use IDs like error-edit-name
    const editErrorTexts = document.querySelectorAll(
        ".edituser_wrapper .error-text"
    );

    if (!editUserForm) return;

    editUserForm.addEventListener("submit", function (e) {
        e.preventDefault();

        // Clear previous messages and errors
        if (editUserMessage) {
            editUserMessage.textContent = "";
            editUserMessage.classList.remove("success");
        }
        editErrorTexts.forEach((el) => (el.textContent = ""));

        const formData = new FormData(editUserForm);

        fetch(editUserForm.action, {
            method: "POST", // Laravel will read _method=PUT
            headers: {
                "X-CSRF-TOKEN": formData.get("_token"),
                Accept: "application/json",
            },
            body: formData,
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.status === "error") {
                    // ❌ Validation Errors: Display them below the fields

                    // Loop through all error messages and place them by field ID
                    Object.keys(data.errors).forEach((key) => {
                        // Assuming edit form error IDs are error-field_name
                        const errorElement = document.getElementById(
                            `error-edit-${key}`
                        );
                        if (errorElement) {
                            errorElement.textContent = data.errors[key][0];
                        }
                    });

                    // Set general error message
                    if (editUserMessage) {
                        editUserMessage.textContent =
                            "Please correct the errors above.";
                        editUserMessage.style.color = "red";
                    }
                } else if (data.status === "success") {
                    // ✅ Success message
                    if (editUserMessage) {
                        editUserMessage.textContent = data.message;
                        editUserMessage.classList.add("success");
                        editUserMessage.style.color = "green";
                    }
                    // Typically, after a successful edit, you'd update the row in the main table
                    // and optionally close the modal/redirect the user.
                }
            })
            .catch((err) => {
                if (editUserMessage) {
                    editUserMessage.textContent = "Something went wrong!";
                    editUserMessage.style.color = "red";
                }
                console.error(err);
            });
    });
});

// --- Import Modal Script ---
const importModal = document.getElementById("import_modal");
const openImportBtn = document.getElementById("openImportModal");
const closeImportBtn = document.getElementById("import_close");
const cancelImportBtn = document.getElementById("import_cancel");
const confirmImportBtn = document.getElementById("import_confirm");
const csvInput = document.getElementById("csv_file");
const fileLabel = document.getElementById("file_label");

if (openImportBtn) {
    openImportBtn.addEventListener("click", () => {
        if (importModal) importModal.style.display = "flex";
    });
}

if (closeImportBtn) {
    closeImportBtn.addEventListener("click", () => {
        if (importModal) importModal.style.display = "none";
    });
}

if (cancelImportBtn) {
    cancelImportBtn.addEventListener("click", () => {
        if (importModal) importModal.style.display = "none";
    });
}

window.addEventListener("click", (e) => {
    if (e.target === importModal) {
        if (importModal) importModal.style.display = "none";
    }
});

// Show selected filename
if (fileLabel && csvInput) {
    // Open file dialog when clicking the label
    fileLabel.addEventListener("click", () => csvInput.click());

    // Update label with selected file name
    csvInput.addEventListener("change", () => {
        const fileName = csvInput.files.length
            ? csvInput.files[0].name
            : "No file chosen";
        fileLabel.textContent = `Choose File: ${fileName}`;
    });
}

// --- Toast Notification Animation ---
document.addEventListener("DOMContentLoaded", () => {
    const toast = document.getElementById("toast");
    if (toast) {
        setTimeout(() => {
            toast.classList.remove("show");
        }, 4000);
    }
});

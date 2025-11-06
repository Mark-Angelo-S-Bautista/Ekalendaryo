// ==========================
// --- ADD DEPARTMENT ---
// ==========================
const addDeptModal = document.getElementById("adddept_overlay");
const addDeptForm = document.getElementById("addDepartmentForm");
const addDeptMessage = document.getElementById("addDeptMessage");
const existingDeptList = document.querySelector(".adddept_list ul");
const addUserDeptSelect = document.getElementById("department"); // Add User modal dropdown

const closeAddDeptModal = () => {
    addDeptModal.style.opacity = "0";
    setTimeout(() => (addDeptModal.style.display = "none"), 200);
};
window.closeAddDeptModal = closeAddDeptModal;

document.getElementById("openAddDept")?.addEventListener("click", () => {
    addDeptModal.style.display = "flex";
    addDeptModal.style.opacity = "0";
    setTimeout(() => (addDeptModal.style.opacity = "1"), 10);
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

addDeptForm?.addEventListener("submit", function (e) {
    e.preventDefault();
    addDeptMessage.textContent = "";
    addDeptMessage.classList.remove("success");

    const formData = new FormData(addDeptForm);

    fetch(addDeptForm.action, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": formData.get("_token"),
            Accept: "application/json",
        },
        body: formData,
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.status === "error") {
                addDeptMessage.textContent = data.errors.join(", ");
                addDeptMessage.classList.remove("success");
                addDeptModal.style.display = "flex";
                addDeptModal.style.opacity = "1";
            } else {
                addDeptMessage.textContent = data.message;
                addDeptMessage.classList.add("success");

                addDeptForm.reset();

                // Add to Existing Departments
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
            }
        })
        .catch((err) => {
            addDeptMessage.textContent = "Something went wrong!";
            addDeptMessage.classList.remove("success");
            console.error(err);
        });
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
    }
};
window.openAddUserModal = openAddUserModal;
window.closeAddUserModal = closeAddUserModal;
window.addEventListener("click", (e) => {
    if (e.target === addUserOverlay) closeAddUserModal();
});

// Show/hide yearlevel & section based on department
const addUserDeptField = addUserForm.querySelector("#department");
const yearLevelFieldAdd = addUserForm
    .querySelector('select[name="yearlevel"]')
    .closest(".adduser_form-group");
const sectionFieldAdd = addUserForm
    .querySelector('input[name="section"]')
    .closest(".adduser_form-group");

const updateAddUserForm = () => {
    const selectedDept = addUserDeptField.value.toUpperCase();
    if (selectedDept.includes("OFFICES")) {
        yearLevelFieldAdd.style.display = "none";
        sectionFieldAdd.style.display = "none";
    } else {
        yearLevelFieldAdd.style.display = "block";
        sectionFieldAdd.style.display = "block";
    }
};
updateAddUserForm();
addUserDeptField.addEventListener("change", updateAddUserForm);

addUserForm?.addEventListener("submit", function (e) {
    e.preventDefault();
    if (addUserMessage) {
        addUserMessage.textContent = "";
        addUserMessage.classList.remove("success");
    }
    const formData = new FormData(addUserForm);

    fetch(addUserForm.action, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": formData.get("_token"),
            Accept: "application/json",
        },
        body: formData,
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.status === "error") {
                addUserMessage.textContent = data.errors.join(", ");
                addUserMessage.classList.remove("success");
                addUserOverlay.style.display = "flex";
                addUserOverlay.style.opacity = "1";
            } else {
                addUserMessage.textContent = data.message;
                addUserMessage.classList.add("success");

                if (usersTableBody && data.user) {
                    const newRow = document.createElement("tr");
                    newRow.setAttribute("data-user-id", data.user.id);
                    newRow.innerHTML = `
                        <td>${data.user.name}<br>${data.user.title}</td>
                        <td>${data.user.userId}</td>
                        <td>${data.user.email}</td>
                        <td>${data.user.department}</td>
                        <td>${data.user.yearlevel || "-"}</td>
                        <td>${data.user.section || "-"}</td>
                        <td>${data.user.role}</td>
                    `;
                    usersTableBody.appendChild(newRow);
                }
                addUserForm.reset();
                updateAddUserForm();
            }
        })
        .catch((err) => {
            addUserMessage.textContent = "Something went wrong!";
            addUserMessage.classList.remove("success");
            console.error(err);
        });
});

// ==========================
// --- EDIT USER ---
// ==========================
document.addEventListener("DOMContentLoaded", () => {
    const editUserForm = document.querySelector(".edituser_wrapper form");
    const editUserMessage = document.getElementById("editUserMessage");

    if (!editUserForm) return;

    editUserForm.addEventListener("submit", function (e) {
        e.preventDefault();

        if (editUserMessage) {
            editUserMessage.textContent = "";
            editUserMessage.classList.remove("success");
        }

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
                    editUserMessage.textContent = data.errors.join(", ");
                } else if (data.status === "success") {
                    editUserMessage.textContent = data.message;
                    editUserMessage.classList.add("success");
                }
            })
            .catch((err) => {
                editUserMessage.textContent = "Something went wrong!";
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

openImportBtn.addEventListener("click", () => {
    importModal.style.display = "flex";
});

closeImportBtn.addEventListener("click", () => {
    importModal.style.display = "none";
});

cancelImportBtn.addEventListener("click", () => {
    importModal.style.display = "none";
});

window.addEventListener("click", (e) => {
    if (e.target === importModal) {
        importModal.style.display = "none";
    }
});

// Show selected filename

// Open file dialog when clicking the label
fileLabel.addEventListener("click", () => csvInput.click());

// Update label with selected file name
csvInput.addEventListener("change", () => {
    const fileName = csvInput.files.length
        ? csvInput.files[0].name
        : "No file chosen";
    fileLabel.textContent = `Choose File: ${fileName}`;
});

// --- Toast Notification Animation ---
document.addEventListener("DOMContentLoaded", () => {
    const toast = document.getElementById("toast");
    if (toast) {
        setTimeout(() => {
            toast.classList.remove("show");
        }, 4000);
    }
});

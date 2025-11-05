// --- Handle Add Department Modal ---
const addDeptModal = document.getElementById("adddept_overlay"); // ✅ fixed ID
if (addDeptModal) {
    const openAddDept = document.getElementById("openAddDept"); // Button to open modal
    const closeAddDept = document.querySelector(".adddept_close"); // X button
    const cancelAddDept = document.querySelector(".adddept_btn.cancel"); // Cancel button

    // ✅ Open modal
    if (openAddDept)
        openAddDept.onclick = () => {
            addDeptModal.style.display = "flex";
            addDeptModal.style.opacity = "0";
            setTimeout(() => (addDeptModal.style.opacity = "1"), 10);
        };

    // ✅ Close modal function with fade animation
    const closeModal = () => {
        addDeptModal.style.opacity = "0";
        setTimeout(() => (addDeptModal.style.display = "none"), 200);
    };

    // ✅ Close via buttons
    if (closeAddDept) closeAddDept.onclick = closeModal;
    if (cancelAddDept) cancelAddDept.onclick = closeModal;

    // ✅ Close by clicking outside
    window.addEventListener("click", (e) => {
        if (e.target === addDeptModal) closeModal();
    });

    // ✅ Expose close function globally (for onclick in HTML)
    window.closeAddDeptModal = closeModal;
}

// --- Handle Add User Modal ---
const addUserOverlay = document.getElementById("adduser_overlay");

window.openAddUserModal = function () {
    if (addUserOverlay) {
        addUserOverlay.style.display = "flex";
        addUserOverlay.style.opacity = "0";
        setTimeout(() => (addUserOverlay.style.opacity = "1"), 10);
    }
};

window.closeAddUserModal = function () {
    if (addUserOverlay) {
        addUserOverlay.style.opacity = "0";
        setTimeout(() => (addUserOverlay.style.display = "none"), 200);
    }
};

// Toast Notification Animation
document.addEventListener("DOMContentLoaded", () => {
    const toast = document.getElementById("toast");
    if (toast) {
        setTimeout(() => {
            toast.classList.remove("show");
        }, 4000); // Hide after 4 seconds
    }
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

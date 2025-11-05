// === Create Event Modal ===
const openModal_btn = document.getElementById("openModalBtn");
const closeModalBtn = document.getElementById("closeModalBtn");
const modalOverlay = document.getElementById("modalOverlay");
openModal_btn.addEventListener("click", () =>
    modalOverlay.classList.add("show")
);
closeModalBtn.addEventListener("click", () =>
    modalOverlay.classList.remove("show")
);
window.addEventListener("click", (e) => {
    if (e.target == modalOverlay) modalOverlay.classList.remove("show");
});

// === Edit Event Modal ===
const editModal_btn = document.getElementById("editModal_btn");
const edit_closeModalBtn = document.getElementById("edit_closeModalBtn");
const edit_modalOverlay = document.getElementById("edit_modalOverlay");
editModal_btn.addEventListener("click", () =>
    edit_modalOverlay.classList.add("show")
);
edit_closeModalBtn.addEventListener("click", () =>
    edit_modalOverlay.classList.remove("show")
);
window.addEventListener("click", (e) => {
    if (e.target == edit_modalOverlay)
        edit_modalOverlay.classList.remove("show");
});

// === Select All Functionality (Create + Edit) ===
function setupSelectAll(selectAllId, checkboxClass) {
    const selectAll = document.getElementById(selectAllId);
    const checkboxes = document.querySelectorAll(
        `#${
            selectAllId.includes("create")
                ? "modalOverlay"
                : "edit_modalOverlay"
        } .${checkboxClass}`
    );

    selectAll.addEventListener("change", function () {
        checkboxes.forEach((cb) => (cb.checked = this.checked));
    });

    checkboxes.forEach((cb) => {
        cb.addEventListener("change", function () {
            if (!this.checked) selectAll.checked = false;
            else if ([...checkboxes].every((c) => c.checked))
                selectAll.checked = true;
        });
    });
}

setupSelectAll("select_all_create", "syear");
setupSelectAll("select_all_edit", "syear");

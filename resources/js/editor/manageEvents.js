// TARGET DEPARTMENT AND TARGET USERS ON THE OFFICES
document.addEventListener("DOMContentLoaded", () => {
    const targetUsersSelect = document.getElementById("targetUsers");
    const yearLevelsContainer = document.getElementById(
        "targetYearLevelsContainer"
    );

    function toggleYearLevels() {
        if (targetUsersSelect.value === "Students") {
            yearLevelsContainer.style.display = "block";
        } else {
            yearLevelsContainer.style.display = "none";

            // Uncheck all checkboxes when hidden
            yearLevelsContainer
                .querySelectorAll('input[type="checkbox"]')
                .forEach((cb) => (cb.checked = false));
        }
    }

    // Run on change
    targetUsersSelect.addEventListener("change", toggleYearLevels);

    // Initial toggle in case the form is pre-filled
    toggleYearLevels();

    // Existing select all logic
    const selectAllCheckbox = document.getElementById("select_all_create");
    const yearCheckboxes = document.querySelectorAll(".syear");

    selectAllCheckbox.addEventListener("change", () => {
        yearCheckboxes.forEach(
            (cb) => (cb.checked = selectAllCheckbox.checked)
        );
    });

    yearCheckboxes.forEach((cb) => {
        cb.addEventListener("change", () => {
            if (![...yearCheckboxes].every((cb) => cb.checked)) {
                selectAllCheckbox.checked = false;
            } else {
                selectAllCheckbox.checked = true;
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const viewButtons = document.querySelectorAll(".btn-view-details");
    const modal = document.getElementById("viewDetailsModal");
    const content = document.getElementById("detailsContent");
    const closeBtn = document.getElementById("closeViewDetailsBtn");

    viewButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            content.innerHTML =
                btn.dataset.details || "No additional details available.";
            modal.style.display = "flex";
        });
    });

    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });
});

// MODAL FOR THE ADD MORE DETAILS FOR THE EVENT
document.addEventListener("DOMContentLoaded", function () {
    const openModalBtn = document.getElementById("openModalBtn");
    const modalOverlay = document.getElementById("modalOverlay");
    const closeModalBtn = document.getElementById("closeModalBtn");

    const openDetailsBtn = document.getElementById("openDetailsModalBtn");
    const detailsOverlay = document.getElementById("detailsModalOverlay");
    const closeDetailsBtn = document.getElementById("closeDetailsModalBtn");
    const saveDetailsBtn = document.getElementById("saveDetailsBtn");
    const detailsTextarea = document.getElementById("detailsTextarea");
    const hiddenDetailsInput = document.getElementById("moreDetailsInput");

    // --- Open/Close Create Event Modal ---
    openModalBtn.addEventListener("click", () => {
        modalOverlay.style.display = "flex";
    });

    closeModalBtn.addEventListener("click", () => {
        modalOverlay.style.display = "none";
    });

    // --- Open More Details Modal ---
    openDetailsBtn.addEventListener("click", () => {
        detailsOverlay.style.display = "flex";
        // Pre-fill textarea with existing value if already entered
        detailsTextarea.value = hiddenDetailsInput.value || "";
    });

    closeDetailsBtn.addEventListener("click", () => {
        detailsOverlay.style.display = "none";
    });

    // --- Save More Details ---
    saveDetailsBtn.addEventListener("click", () => {
        // Save value to hidden input
        hiddenDetailsInput.value = detailsTextarea.value;
        // Close More Details modal and return to Create Event modal
        detailsOverlay.style.display = "none";
    });
});

//toast notification
document.addEventListener("DOMContentLoaded", function () {
    const toast = document.getElementById("toast");
    if (toast) {
        setTimeout(() => {
            toast.style.opacity = "0";
            toast.style.transform = "translateY(20px)";
            toast.style.visibility = "hidden";
            setTimeout(() => toast.remove(), 500); // remove after animation
        }, 4000);
    }
});

// CREATE MODAL
const openModalBtn = document.getElementById("openModalBtn");
const modalOverlay = document.getElementById("modalOverlay");
const closeModalBtn = document.getElementById("closeModalBtn");

openModalBtn.addEventListener("click", () => {
    modalOverlay.style.display = "flex";
});

closeModalBtn.addEventListener("click", () => {
    modalOverlay.style.display = "none";
});

// EDIT MODAL
const editButtons = document.querySelectorAll(".event-card .edit");
const editModal = document.getElementById("edit_modalOverlay");
const closeEditModalBtn = document.getElementById("edit_closeModalBtn");
const editForm = document.getElementById("editEventForm");

editButtons.forEach((button) => {
    button.addEventListener("click", () => {
        // Show modal
        editModal.style.display = "flex";

        // Populate fields
        document.getElementById("edit_title").value = button.dataset.title;
        document.getElementById("edit_description").value =
            button.dataset.description;
        document.getElementById("edit_date").value = button.dataset.date;
        document.getElementById("edit_start_time").value =
            button.dataset.start_time;
        document.getElementById("edit_end_time").value =
            button.dataset.end_time;
        document.getElementById("edit_location").value =
            button.dataset.location;

        // Populate checkboxes
        const targetYears = JSON.parse(
            button.dataset.target_year_levels || "[]"
        );
        ["1st Year", "2nd Year", "3rd Year", "4th Year"].forEach(
            (year, index) => {
                document.getElementById(`edit_year${index + 1}`).checked =
                    targetYears.includes(year);
            }
        );

        // Set form action dynamically
        editForm.action = `/editor/manageEvents/${button.dataset.id}`;
    });
});

closeEditModalBtn.addEventListener("click", () => {
    editModal.style.display = "none";
});

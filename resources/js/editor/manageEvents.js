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
//toast Notification

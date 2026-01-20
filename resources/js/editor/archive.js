// Ensure the DOM is fully loaded
document.addEventListener("DOMContentLoaded", () => {
    // Open a modal by ID
    window.openModal = function (id) {
        const modal = document.getElementById(id);
        if (modal) modal.style.display = "flex";
    };

    // Close a modal by ID
    window.closeModal = function (id) {
        const modal = document.getElementById(id);
        if (modal) modal.style.display = "none";
    };

    // Close modal when clicking outside modal content
    window.addEventListener("click", (e) => {
        document.querySelectorAll(".modal").forEach((modal) => {
            if (e.target === modal) modal.style.display = "none";
        });
    });
});

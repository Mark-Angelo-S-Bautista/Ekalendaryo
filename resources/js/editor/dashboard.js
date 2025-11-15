document.addEventListener("DOMContentLoaded", function () {
    // Modal elements
    const detailsModal = document.getElementById(
        "dashboardDetailsModalOverlay"
    );
    const detailsTextarea = document.getElementById("dashboardDetailsTextarea");
    const detailsCloseBtn = document.getElementById("dashboardDetailsCloseBtn");

    // If modal elements are missing, warn and exit early
    if (!detailsModal || !detailsTextarea || !detailsCloseBtn) {
        console.warn("Dashboard details modal elements missing.");
        return;
    }

    // Ensure modal is full-page and on top
    detailsModal.style.zIndex = "99999";
    detailsModal.style.display = "none";
    detailsModal.style.justifyContent = "center";
    detailsModal.style.alignItems = "center";

    // Event delegation: listen on document for clicks on .dashboard_view_btn
    document.addEventListener("click", function (e) {
        const btn = e.target.closest && e.target.closest(".dashboard_view_btn");
        if (!btn) return;

        // read the data-details attribute (it contains literal backslash + n sequences)
        let raw = btn.dataset.details || "No additional details.";

        // Convert escaped sequences \n into real newlines
        raw = raw.replace(/\\n/g, "\n");

        // Optional: convert any literal escaped HTML entities back (if necessary)
        // raw = raw.replace(/&lt;br&gt;/g, '\n');

        // Trim and set in textarea
        detailsTextarea.value = raw.trim();

        // Make modal visible and ensure it appears on top
        detailsModal.style.display = "flex";
        // Focus into textarea for convenience (optional)
        detailsTextarea.focus();
    });

    // Close handlers
    detailsCloseBtn.addEventListener("click", () => {
        detailsModal.style.display = "none";
    });

    // Close when clicking overlay background
    window.addEventListener("click", (e) => {
        if (e.target === detailsModal) {
            detailsModal.style.display = "none";
        }
    });

    // If you want Esc to close the modal:
    window.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && detailsModal.style.display === "flex") {
            detailsModal.style.display = "none";
        }
    });
});

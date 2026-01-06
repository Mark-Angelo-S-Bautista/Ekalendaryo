document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("changeSchoolYearBtn");
    if (!btn) return;

    btn.addEventListener("click", async () => {
        if (!confirm("Change school year now?")) return;

        const res = await fetch("/usermanagement/school-year/change", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
                Accept: "application/json",
            },
        });

        const data = await res.json();
        alert(data.message);
        location.reload();
    });
});

const dashboardModal = document.getElementById("dashboard_department_modal");
const dashboardOpen = document.getElementById("dashboard_department_box");
const dashboardClose = document.getElementById("dashboard_close_modal");

dashboardOpen.addEventListener("click", () => {
    dashboardModal.style.display = "flex";
});

dashboardClose.addEventListener("click", () => {
    dashboardModal.style.display = "none";
});

window.addEventListener("click", (e) => {
    if (e.target === dashboardModal) {
        dashboardModal.style.display = "none";
    }
});

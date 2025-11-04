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

function filterActivities() {
    const filter = document.getElementById("filterSelect").value.toLowerCase();
    const cards = document.querySelectorAll(".activity-card");
    cards.forEach((card) => {
        const status = card.dataset.status;
        card.style.display =
            filter === "all" || status === filter ? "block" : "none";
    });
}
function filterActivities() {
    const filter = document.getElementById("filterSelect").value;
    const cards = document.querySelectorAll(
        ".activity-section > div[data-status]"
    );

    cards.forEach((card) => {
        if (filter === "all" || card.dataset.status === filter) {
            card.style.display = "block";
        } else {
            card.style.display = "none";
        }
    });
}

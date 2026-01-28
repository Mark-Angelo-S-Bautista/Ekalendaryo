function filterActivities() {
    const filter = document.getElementById("filterSelect").value.toLowerCase();
    const cards = document.querySelectorAll(".activity-card");
    cards.forEach((card) => {
        const status = card.dataset.status;
        card.style.display =
            filter === "all" || status === filter ? "block" : "none";
    });
}

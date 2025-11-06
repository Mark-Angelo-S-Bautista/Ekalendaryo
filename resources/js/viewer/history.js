const searchInput = document.getElementById("search");
const filterSelect = document.getElementById("filterType");
const events = document.querySelectorAll(".event-card");

function filterEvents() {
    const query = searchInput.value.toLowerCase();
    const filter = filterSelect.value;

    events.forEach((event) => {
        const matchesSearch = event.innerText.toLowerCase().includes(query);
        const matchesFilter =
            filter === "All Types" || event.dataset.type === filter;
        event.style.display = matchesSearch && matchesFilter ? "block" : "none";
    });
}

function clearFilters() {
    searchInput.value = "";
    filterSelect.value = "All Types";
    filterEvents();
}

searchInput.addEventListener("input", filterEvents);
filterSelect.addEventListener("change", filterEvents);

// Feedback Modal Logic
const modalOverlay = document.getElementById("feedbackModal");
const closeModal = document.getElementById("closeModal");
const modalTitle = document.getElementById("modalTitle");
const feedbackButtons = document.querySelectorAll(".feedback-btn");

feedbackButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
        const card = btn.closest(".event-card");
        const title = card.dataset.title;
        modalTitle.textContent = `Leave Feedback for ${title}`;
        modalOverlay.style.display = "flex";
    });
});

closeModal.addEventListener("click", () => {
    modalOverlay.style.display = "none";
});

window.addEventListener("click", (e) => {
    if (e.target === modalOverlay) {
        modalOverlay.style.display = "none";
    }
});

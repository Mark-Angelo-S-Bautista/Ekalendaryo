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

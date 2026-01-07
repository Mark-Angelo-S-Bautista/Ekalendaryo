// Search events by title or department
const searchInput = document.getElementById("search");
const clearBtn = document.getElementById("clearSearch");
const eventList = document.getElementById("eventList");

searchInput.addEventListener("input", function () {
    const query = this.value.toLowerCase();
    const events = eventList.querySelectorAll(".event-card");

    events.forEach((event) => {
        const title = event.querySelector("h3").textContent.toLowerCase();
        const department = event
            .querySelector(".tag")
            .textContent.toLowerCase();

        if (title.includes(query) || department.includes(query)) {
            event.style.display = "block";
        } else {
            event.style.display = "none";
        }
    });
});

// Clear search input
clearBtn.addEventListener("click", function () {
    searchInput.value = "";
    const events = eventList.querySelectorAll(".event-card");
    events.forEach((event) => (event.style.display = "block"));
});

//FEEDBACK JS
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("feedbackModal");
    const feedbackList = document.getElementById("feedbackList");
    const closeBtn = modal.querySelector(".close-btn");

    let currentEventId = null;

    // Open modal and load feedback
    document.querySelectorAll(".feedback-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            currentEventId = this.dataset.eventId;
            loadFeedback(1); // Load first page
            modal.style.display = "block";
        });
    });

    // Close modal
    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
        feedbackList.innerHTML = "";
    });

    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
            feedbackList.innerHTML = "";
        }
    });

    // Load feedback via AJAX
    function loadFeedback(page = 1) {
        fetch(`/editor/event/${currentEventId}/feedback?page=${page}`)
            .then((res) => res.text())
            .then((html) => {
                feedbackList.innerHTML = html;
                setupPagination();
            })
            .catch(() => {
                feedbackList.innerHTML = "<p>Error loading feedback.</p>";
            });
    }

    // Setup pagination buttons inside the modal
    function setupPagination() {
        const prevBtn = feedbackList.querySelector(".prev-page");
        const nextBtn = feedbackList.querySelector(".next-page");

        if (prevBtn) {
            prevBtn.addEventListener("click", () => {
                loadFeedback(prevBtn.dataset.page);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener("click", () => {
                loadFeedback(nextBtn.dataset.page);
            });
        }
    }
});

// Clear filters button
document.getElementById("clearFilters").addEventListener("click", () => {
    document.getElementById("search").value = "";
    document.getElementById("filterType").value = "All";
});

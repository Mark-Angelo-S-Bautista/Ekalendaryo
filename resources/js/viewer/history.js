const commentInput = document.getElementById("comment");
const charCounter = document.getElementById("charCounter");
const charError = document.getElementById("charError");
const submitBtn = document.getElementById("feedbackSubmitBtn");
const maxChars = 1000;

// Update counter on input
commentInput.addEventListener("input", () => {
    const length = commentInput.value.length;
    charCounter.textContent = `${length} / ${maxChars} characters`;

    if (length > maxChars) {
        charError.style.display = "block";
        submitBtn.disabled = true;
        submitBtn.style.opacity = "0.5"; // optional visual cue
    } else {
        charError.style.display = "none";
        submitBtn.disabled = false;
        submitBtn.style.opacity = "1";
    }
});

document.addEventListener("DOMContentLoaded", function () {
    // ===== Search & Clear =====
    const searchInput = document.getElementById("search");
    const clearBtn = document.getElementById("clearSearch");
    const eventList = document.getElementById("eventList");

    if (searchInput) {
        searchInput.addEventListener("input", function () {
            const query = this.value.toLowerCase();
            const events = eventList.querySelectorAll(".event-card");

            events.forEach((event) => {
                const title = event
                    .querySelector("h3")
                    .textContent.toLowerCase();
                const department = event
                    .querySelector(".tag")
                    .textContent.toLowerCase();
                event.style.display =
                    title.includes(query) || department.includes(query)
                        ? "block"
                        : "none";
            });
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener("click", function () {
            searchInput.value = "";
            const events = eventList.querySelectorAll(".event-card");
            events.forEach((event) => (event.style.display = "block"));
        });
    }

    // ===== Feedback Modal =====
    const modal = document.getElementById("feedbackModal");
    const feedbackList = document.getElementById("feedbackList");
    const closeBtn = modal?.querySelector(".close-btn");
    const feedbackForm = document.getElementById("feedbackForm");
    const feedbackEventId = document.getElementById("feedbackEventId");
    const feedbackEventTitle = document.getElementById("feedbackEventTitle");

    let currentEventId = null;

    // Only attach modal opening to event buttons
    document.querySelectorAll(".feedback-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            currentEventId = this.dataset.eventId;
            feedbackEventId.value = currentEventId;

            if (feedbackEventTitle) {
                feedbackEventTitle.textContent =
                    this.dataset.eventTitle + " - Feedback";
            }

            loadFeedback(1);
            modal.style.display = "block";
        });
    });

    // Close modal
    closeBtn?.addEventListener("click", () => {
        modal.style.display = "none";
        feedbackList.innerHTML = "";
    });

    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
            feedbackList.innerHTML = "";
        }
    });

    // ===== Feedback AJAX Load =====
    function loadFeedback(page = 1) {
        if (!currentEventId) return;

        fetch(`/viewer/events/${currentEventId}/feedback?page=${page}`)
            .then((res) => res.text())
            .then((html) => {
                feedbackList.innerHTML = html;
                setupPagination();
            })
            .catch(() => {
                feedbackList.innerHTML = "<p>Error loading feedback.</p>";
            });
    }

    function setupPagination() {
        const prevBtn = feedbackList.querySelector(".prev-page");
        const nextBtn = feedbackList.querySelector(".next-page");

        if (prevBtn) {
            prevBtn.addEventListener("click", () =>
                loadFeedback(prevBtn.dataset.page)
            );
        }
        if (nextBtn) {
            nextBtn.addEventListener("click", () =>
                loadFeedback(nextBtn.dataset.page)
            );
        }
    }

    // ===== Feedback Form Submission =====
    feedbackForm?.addEventListener("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(feedbackForm);

        fetch(feedbackForm.action, {
            method: "POST",
            body: formData, // CSRF included automatically
        })
            .then((res) => {
                if (!res.ok) throw new Error("Network response was not ok");
                return res.json();
            })
            .then((data) => {
                const toast = document.getElementById("toast");
                if (data.success) {
                    // Close modal
                    modal.style.display = "none";

                    // Change the feedback button for this event
                    const btn = document.querySelector(
                        `.feedback-btn[data-event-id="${currentEventId}"]`
                    );
                    if (btn) {
                        btn.textContent = "✔️ Feedback Submitted";
                        btn.style.backgroundColor = "#22c55e"; // green
                        btn.disabled = true;
                        btn.classList.add("submitted");
                    }

                    // Show success toast
                    if (toast) {
                        toast.textContent = data.message;
                        toast.style.backgroundColor = "#22c55e"; // green
                        toast.style.display = "block";
                        toast.style.opacity = "1";

                        setTimeout(() => {
                            toast.style.opacity = "0";
                            setTimeout(() => {
                                toast.style.display = "none";
                            }, 500);
                        }, 2500); // visible for 2.5 seconds
                    }
                } else {
                    // Show error toast
                    if (toast) {
                        toast.textContent =
                            data.message || "Error submitting feedback.";
                        toast.style.backgroundColor = "#ef4444"; // red
                        toast.style.display = "block";
                        toast.style.opacity = "1";

                        setTimeout(() => {
                            toast.style.opacity = "0";
                            setTimeout(() => {
                                toast.style.display = "none";
                                toast.style.backgroundColor = "#22c55e"; // reset to green
                            }, 500);
                        }, 2500);
                    }
                }
            })
            .catch(() => {
                const toast = document.getElementById("toast");
                if (toast) {
                    toast.textContent = "Error submitting feedback.";
                    toast.style.backgroundColor = "#ef4444"; // red
                    toast.style.display = "block";
                    toast.style.opacity = "1";

                    setTimeout(() => {
                        toast.style.opacity = "0";
                        setTimeout(() => {
                            toast.style.display = "none";
                            toast.style.backgroundColor = "#22c55e"; // reset to green
                        }, 500);
                    }, 2500);
                }
            });
    });
});

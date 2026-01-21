// ==========================
// Search Events
// ==========================
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

clearBtn.addEventListener("click", function () {
    searchInput.value = "";
    const events = eventList.querySelectorAll(".event-card");
    events.forEach((event) => (event.style.display = "block"));
});

// ==========================
// Feedback Modal
// ==========================
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("feedbackModal");
    const feedbackList = document.getElementById("feedbackList");
    const closeBtn = modal.querySelector(".close-btn");
    let currentEventId = null;

    document.querySelectorAll(".feedback-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            currentEventId = this.dataset.eventId;
            loadFeedback(1); // Load first page
            modal.style.display = "block";
        });
    });

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

    function setupPagination() {
        const prevBtn = feedbackList.querySelector(".prev-page");
        const nextBtn = feedbackList.querySelector(".next-page");

        if (prevBtn) {
            prevBtn.addEventListener("click", () =>
                loadFeedback(prevBtn.dataset.page),
            );
        }

        if (nextBtn) {
            nextBtn.addEventListener("click", () =>
                loadFeedback(nextBtn.dataset.page),
            );
        }
    }
});

// ==========================
// Clear Filters Button
// ==========================
document.getElementById("clearFilters")?.addEventListener("click", () => {
    document.getElementById("search").value = "";
    document.getElementById("filterType").value = "All";
});

// ==========================
// Report Modal & Upload
// ==========================
const reportModal = document.getElementById("reportModal");
const reportCloseBtn = document.getElementById("reportCloseBtn");
const reportForm = document.getElementById("reportForm");
const reportInput = document.getElementById("reportInput");
const reportEventId = document.getElementById("reportEventId");
const selectedFile = document.getElementById("selectedFile");

// Open modal when upload button clicked
document.querySelectorAll(".upload-report-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
        reportEventId.value = this.dataset.eventId;
        reportForm.action = `/editor/events/${this.dataset.eventId}/upload-report`;
        reportInput.value = "";
        selectedFile.textContent = "No file selected";
        reportModal.style.display = "block";
    });
});

// Show selected file name
reportInput.addEventListener("change", function () {
    if (this.files && this.files.length > 0) {
        selectedFile.textContent = `Selected File: ${this.files[0].name}`;
    } else {
        selectedFile.textContent = "No file selected";
    }
});

// AJAX form submission
reportForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch(this.action, {
        method: "POST",
        body: formData,
        headers: {
            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]')
                .value,
            "X-Requested-With": "XMLHttpRequest",
        },
    })
        .then((res) => {
            if (!res.ok) throw new Error("Upload failed");
            return res.json();
        })
        .then((data) => {
            if (data.success) {
                alert(data.message);
                reportModal.style.display = "none";
                reportInput.value = "";
                selectedFile.textContent = "No file selected";

                // Replace Upload button with Download link
                const btn = document.querySelector(
                    `.upload-report-btn[data-event-id="${reportEventId.value}"]`,
                );
                if (btn) {
                    const downloadLink = document.createElement("a");
                    downloadLink.href = data.downloadUrl;
                    downloadLink.textContent = "Download Report";
                    downloadLink.classList.add("report-btn");
                    downloadLink.style.marginLeft = "10px";
                    btn.parentNode.replaceChild(downloadLink, btn);
                }
            }
        })
        .catch((err) => {
            alert("Failed to upload report. Try again.");
            console.error(err);
        });
});

// Close modal
reportCloseBtn.addEventListener("click", () => {
    reportModal.style.display = "none";
    reportInput.value = "";
    selectedFile.textContent = "No file selected";
});

// Close modal if clicking outside
window.addEventListener("click", (e) => {
    if (e.target === reportModal) {
        reportModal.style.display = "none";
        reportInput.value = "";
        selectedFile.textContent = "No file selected";
    }
});

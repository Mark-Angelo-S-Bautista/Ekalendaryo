// ==========================
// Search Events
// ==========================
const searchInput = document.getElementById("search");
const clearBtn = document.getElementById("clearSearch");
const eventList = document.getElementById("eventList");

searchInput.addEventListener("input", function () {
    const query = this.value.toLowerCase();
    eventList.querySelectorAll(".event-card").forEach((event) => {
        const title = event.querySelector("h3").textContent.toLowerCase();
        const department = event
            .querySelector(".tag")
            .textContent.toLowerCase();
        event.style.display =
            title.includes(query) || department.includes(query)
                ? "block"
                : "none";
    });
});

clearBtn.addEventListener("click", function () {
    searchInput.value = "";
    eventList
        .querySelectorAll(".event-card")
        .forEach((event) => (event.style.display = "block"));
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
            loadFeedback(1);
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

        if (prevBtn)
            prevBtn.addEventListener("click", () =>
                loadFeedback(prevBtn.dataset.page),
            );
        if (nextBtn)
            nextBtn.addEventListener("click", () =>
                loadFeedback(nextBtn.dataset.page),
            );
    }
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

// Show selected file name
reportInput.addEventListener("change", function () {
    selectedFile.textContent =
        this.files && this.files.length > 0
            ? `Selected File: ${this.files[0].name}`
            : "No file selected";
});

// Open modal when upload button clicked
function attachUploadButtons() {
    document.querySelectorAll(".upload-report-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            reportEventId.value = this.dataset.eventId;
            reportForm.action = `/editor/events/${this.dataset.eventId}/upload-report`;
            reportInput.value = "";
            selectedFile.textContent = "No file selected";
            reportModal.style.display = "block";
        });
    });
}
attachUploadButtons();

// Remove report from event card
function attachRemoveButtons() {
    document.querySelectorAll(".remove-report-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            const eventId = this.dataset.eventId;
            if (!confirm("Are you sure you want to remove this report?"))
                return;

            const parentSpan = this.parentNode;

            fetch(`/editor/events/${eventId}/remove-report`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'input[name="_token"]',
                    ).value,
                    "X-Requested-With": "XMLHttpRequest",
                },
            })
                .then((res) => res.json())
                .then((data) => {
                    if (data.success) {
                        alert(data.message);

                        // Remove only report buttons, keep feedback
                        parentSpan
                            .querySelectorAll(".report-btn, .remove-report-btn")
                            .forEach((el) => el.remove());

                        // Add back Upload button
                        const uploadBtn = document.createElement("button");
                        uploadBtn.classList.add("upload-report-btn");
                        uploadBtn.dataset.eventId = eventId;
                        uploadBtn.textContent = "Upload Report";
                        uploadBtn.style.marginLeft = "10px";
                        parentSpan.appendChild(uploadBtn);

                        attachUploadButtons(); // Reattach click
                    }
                })
                .catch((err) => console.error(err));
        });
    });
}
attachRemoveButtons();

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

                // Replace only report buttons, keep feedback
                const parentSpan = document.querySelector(
                    `.event-card [data-event-id="${reportEventId.value}"]`,
                ).parentNode;

                parentSpan
                    .querySelectorAll(
                        ".report-btn, .remove-report-btn, .upload-report-btn",
                    )
                    .forEach((el) => el.remove());

                const downloadLink = document.createElement("a");
                downloadLink.href = data.downloadUrl;
                downloadLink.textContent = "Download Report";
                downloadLink.classList.add("report-btn");
                downloadLink.style.marginLeft = "10px";

                const removeBtn = document.createElement("button");
                removeBtn.textContent = "🗑 Remove Report";
                removeBtn.classList.add("remove-report-btn");
                removeBtn.dataset.eventId = reportEventId.value;
                removeBtn.style.marginLeft = "10px";

                parentSpan.appendChild(downloadLink);
                parentSpan.appendChild(removeBtn);

                attachRemoveButtons(); // Reattach click
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

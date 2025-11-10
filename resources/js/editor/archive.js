function openModal(id) {
    document.getElementById(id).style.display = "flex";
}

function closeModal(id) {
    document.getElementById(id).style.display = "none";
}

function filterEvents() {
    const filter = document.getElementById("eventFilter").value;
    document.querySelectorAll("#eventList .event").forEach((ev) => {
        ev.style.display =
            filter === "all" || ev.dataset.type === filter ? "block" : "none";
    });
}

function filterStudents() {
    const filter = document.getElementById("studentFilter").value;
    document.querySelectorAll("#studentList .student").forEach((stu) => {
        stu.style.display =
            filter === "all" || stu.dataset.dept === filter ? "block" : "none";
    });
}

/* ✅ Restore button function */
function restoreItem(button) {
    const item = button.closest(".event");
    alert(`Restored: ${item.querySelector("h4").innerText}`);
    item.remove();
}

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

function restoreItem(button) {
    const item = button.closest(".deleted-user");
    alert(`Restored: ${item.querySelector("h4").innerText}`);
    item.remove();
}

function toggleUserType() {
    const type = document.getElementById("userType").value;
    const deptDropdown = document.getElementById("deptFilter");
    const users = document.querySelectorAll(".deleted-user");

    deptDropdown.style.display = type === "student" ? "inline" : "none";

    users.forEach((u) => {
        if (type === "all") u.style.display = "block";
        else u.style.display = u.dataset.type === type ? "block" : "none";
    });
}

function filterDeleted() {
    const dept = document.getElementById("deptFilter").value;
    const users = document.querySelectorAll(
        '.deleted-user[data-type="student"]'
    );
    users.forEach((u) => {
        u.style.display =
            dept === "all" || u.dataset.dept === dept ? "block" : "none";
    });
}

// 🔍 Search Function for Recently Deleted
function searchDeleted() {
    const query = document.getElementById("deletedSearch").value.toLowerCase();
    const users = document.querySelectorAll(".deleted-user");
    users.forEach((u) => {
        const text = u.innerText.toLowerCase();
        u.style.display = text.includes(query) ? "block" : "none";
    });
}

// Default initialization
(function initDeletedModalDefault() {
    document
        .querySelectorAll(".deleted-user")
        .forEach((u) => (u.style.display = "block"));
})();

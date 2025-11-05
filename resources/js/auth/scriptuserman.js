function openTab(evt, tabName) {
    // Hide all tab contents
    const contents = document.querySelectorAll(".tab-content");
    contents.forEach((content) => content.classList.remove("active"));

    // Remove active class from all buttons
    const links = document.querySelectorAll(".tab-link");
    links.forEach((link) => link.classList.remove("active"));

    // Show the clicked tab
    document.getElementById(tabName).classList.add("active");

    // Mark clicked button as active
    evt.currentTarget.classList.add("active");
}

// DASHBOARD TAB JS

function openComment() {
    // Opening Comments in upcoming event
    document.getElementById("pop_comment").style.display = "flex";
}

function closeComment() {
    // Closing Comments in upcoming event
    document.getElementById("pop_comment").style.display = "none";
}

src = "https://code.jquery.com/jquery-3.5.1.slim.min.js";
src =
    "https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js";
src = "https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js";

// JAVASCRIPT USERS TAB

const users = [
    {
        user: "student1",
        name: "Alex Thompson",
        email: "student1@school.edu",
        role: "Student",
        department: "BSIS-ACT",
    },
    {
        user: "former_student1",
        name: "John Williams",
        email: "former1@school.edu",
        role: "Student",
        department: "BSIS-ACT",
    },
    {
        user: "student2",
        name: "Maria Cruz",
        email: "student2@school.edu",
        role: "Student",
        department: "BSOM",
    },
    {
        user: "student3",
        name: "Jake Lee",
        email: "student3@school.edu",
        role: "Student",
        department: "DHRMT",
    },
    {
        user: "bsis_head",
        name: "Dr. Jane Smith",
        email: "bsis@school.edu",
        role: "Department Head",
    },
    {
        user: "bsom_head",
        name: "Dr. David Martinez",
        email: "bsom@school.edu",
        role: "Department Head",
    },
];

const departments = [
    { name: "BSIS-ACT", count: 2 },
    { name: "BSAIS", count: 2 },
    { name: "BSOM", count: 1 },
    { name: "BTVTED", count: 1 },
    { name: "BSCA", count: 0 },
    { name: "DHRMT", count: 1 },
    { name: "HB", count: 1 },
];

const tableBody = document.querySelector("#userTable tbody");
const cards = document.querySelectorAll(".users_card");
const searchInput = document.querySelector("#search");
const modal = document.querySelector("#users_modal");
const modalClose = document.querySelector("#users_modal_close");
const modalBody = document.querySelector("#users_modal_body");
const activeFilter = document.querySelector("#users_active_filter");
const studentCard = document.querySelector("#studentCard");

function renderTable(role = "All Users", department = null, search = "") {
    tableBody.innerHTML = "";
    const filteredUsers = users.filter(
        (u) =>
            (role === "All Users" || u.role === role) &&
            (!department || u.department === department) &&
            (u.user.toLowerCase().includes(search) ||
                u.email.toLowerCase().includes(search))
    );

    filteredUsers.forEach((u) => {
        const row = document.createElement("tr");
        row.innerHTML = `
          <td>${u.user}<br><small>${u.name}</small></td>
          <td>${u.email}</td>
          <td><span class="users_role">${u.role}</span></td>
          <td class="users_actions">
            <i title="Edit">✏️</i>
            <i title="Delete">🗑️</i>
          </td>
        `;
        tableBody.appendChild(row);
    });
}

renderTable();

modalBody.innerHTML = departments
    .map(
        (dep) =>
            `<div class="users_modal_department" data-dep="${dep.name}">
        <h3>${dep.name}</h3><p>${dep.count} ${
                dep.count === 1 ? "student" : "students"
            }</p>
      </div>`
    )
    .join("");

cards.forEach((card) => {
    card.addEventListener("click", () => {
        cards.forEach((c) => c.classList.remove("active"));
        card.classList.add("active");
        const role = card.getAttribute("data-role");

        if (role === "Student") {
            modal.style.display = "block";
        } else {
            renderActiveFilters([{ text: role }]);
            studentCard.querySelector("p").textContent = "Student";
            renderTable(role);
        }
    });
});

searchInput.addEventListener("input", (e) => {
    const activeCard = document.querySelector(".users_card.active");
    const role = activeCard.getAttribute("data-role");
    renderTable(role, null, e.target.value.toLowerCase());
});

modalClose.onclick = () => (modal.style.display = "none");
window.onclick = (e) => {
    if (e.target === modal) modal.style.display = "none";
};

document.addEventListener("click", (e) => {
    if (e.target.closest(".users_modal_department")) {
        const dep = e.target.closest(".users_modal_department");
        const department = dep.getAttribute("data-dep");
        modal.style.display = "none";
        studentCard.querySelector("p").textContent = `Student (${department})`;

        renderActiveFilters([{ text: "Student" }, { text: department }]);
        renderTable("Student", department);
    }
});

function renderActiveFilters(filters) {
    activeFilter.innerHTML = `<span>Active filters:</span>`;
    filters.forEach((f) => {
        const tag = document.createElement("div");
        tag.className = "users_filter_tag";
        tag.innerHTML = `<span>${f.text}</span><button class="users_filter_close">&times;</button>`;
        tag.querySelector("button").onclick = () => {
            renderActiveFilters([{ text: "All Users" }]);
            studentCard.querySelector("p").textContent = "Student";
            renderTable("All Users");
            document
                .querySelector('[data-role="All Users"]')
                .classList.add("active");
        };
        activeFilter.appendChild(tag);
    });
}

// --- Add Department Modal ---
const addDeptModal = document.getElementById("adddept_modal");
document.getElementById("openAddDept").onclick = () =>
    (addDeptModal.style.display = "flex");
document.getElementById("adddept_close").onclick = () =>
    (addDeptModal.style.display = "none");
document.getElementById("adddept_cancel").onclick = () =>
    (addDeptModal.style.display = "none");
window.onclick = (e) => {
    if (e.target === addDeptModal) addDeptModal.style.display = "none";
};

// --- Add User Modal ---
document.addEventListener("DOMContentLoaded", function () {
    const overlay = document.getElementById("adduser_overlay");
    if (overlay) overlay.style.display = "flex"; // show modal overlay

    // referenced by the Cancel button in the modal
    window.closeAddUserModal = function () {
        if (overlay) overlay.style.display = "none";
        // remove the query param from the URL without reloading
        history.replaceState(null, "", "{{ url()->current() }}");
    };

    // optionally: open modal without navigating (if user clicks the Add link)
    const addLink = document.querySelector("a.users_btn.add");
    if (addLink) {
        addLink.addEventListener("click", function (e) {
            // if href points to same page with ?AddUser=1, prevent navigation and show modal client-side
            const href = addLink.getAttribute("href") || "";
            if (href.includes("AddUser")) {
                e.preventDefault();
                if (overlay) overlay.style.display = "flex";
                history.replaceState(null, "", href);
            }
        });
    }
});

//toast Notification
document.addEventListener("DOMContentLoaded", function () {
    const toast = document.getElementById("toast");
    if (toast) {
        setTimeout(() => {
            toast.style.opacity = "0";
            toast.style.transform = "translateY(20px)";
            toast.style.visibility = "hidden";
            setTimeout(() => toast.remove(), 500); // remove after animation
        }, 4000);
    }
});

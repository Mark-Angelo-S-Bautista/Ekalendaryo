const events = [
    {
        date: "2025-11-05",
        title: "dsad",
        type: "admin",
        description: "adaddsdss",
        timeStart: "15:55",
        timeEnd: "15:55",
        location: "COURT",
        sy: "SY.2025-2026",
        organizer: "admin",
    },
];

let currentMonth = 10; // November
let currentYear = 2025;

const grid = document.getElementById("calendar_grid");
const monthYear = document.getElementById("calendar_monthYear");
const eventFilter = document.getElementById("calendar_eventFilter");

function renderCalendar() {
    grid.innerHTML = "";
    const firstDay = new Date(currentYear, currentMonth, 1);
    const lastDay = new Date(currentYear, currentMonth + 1, 0);
    const startDay = firstDay.getDay();
    const totalDays = lastDay.getDate();

    const monthNames = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
    ];
    monthYear.textContent = `${monthNames[currentMonth]} ${currentYear}`;

    for (let i = 0; i < startDay; i++)
        grid.appendChild(document.createElement("div"));

    for (let d = 1; d <= totalDays; d++) {
        const dayEl = document.createElement("div");
        dayEl.classList.add("calendar_day");
        const date = `${currentYear}-${String(currentMonth + 1).padStart(
            2,
            "0"
        )}-${String(d).padStart(2, "0")}`;
        const num = document.createElement("div");
        num.classList.add("calendar_day-number");
        num.textContent = d;
        dayEl.appendChild(num);

        const filtered = events.filter((ev) => ev.date === date);
        const filterVal = eventFilter.value;
        filtered.forEach((ev) => {
            if (filterVal === "all" || filterVal === ev.type) {
                const e = document.createElement("div");
                e.classList.add("calendar_event", `calendar_${ev.type}`);
                e.textContent = ev.title;
                dayEl.appendChild(e);
            }
        });

        dayEl.addEventListener("click", () => openModal(date));
        grid.appendChild(dayEl);
    }
}

const modal = document.getElementById("calendar_modal");
const modalTitle = document.getElementById("calendar_modal-title");
const modalBody = document.getElementById("calendar_modal-body");
const modalClose = document.getElementById("calendar_close");

function openModal(date) {
    const eventData = events.find((ev) => ev.date === date);
    modal.style.display = "flex";
    modalTitle.textContent = formatDate(date);

    if (!eventData) {
        // No event layout
        modalBody.innerHTML = `
      <div class="calendar_noevent-list">
        ${Array.from({ length: 11 }, (_, i) => `${7 + i}:00 AM`)
            .map((t) => `<div class='calendar_noevent-time'>${t}</div>`)
            .join("")}
      </div>
      <div class="calendar_noevent-footer">
        <img src="https://img.icons8.com/ios-filled/24/aaaaaa/calendar--v1.png"/><br>
        No events scheduled for this day
      </div>`;
    } else {
        // Event details layout
        modalBody.innerHTML = `
      <div class="calendar_event-detail">
        <div class="calendar_event-title">${eventData.title}</div>
        <p style="font-size:13px;color:#666;">Event details and information</p>
        <div class="calendar_badges">
          <span class="calendar_badge admin">${eventData.type}</span>
          <span class="calendar_badge upcoming">upcoming</span>
        </div>
        <div class="calendar_event-description">${eventData.description}</div>
        <div class="calendar_event-info">
          <div>📅 ${formatShortDate(eventData.date)}</div>
          <div>⏰ ${eventData.timeStart} - ${eventData.timeEnd}</div>
          <div>📍 ${eventData.location}</div>
          <div>👤 ${eventData.organizer}</div>
          <div>${eventData.sy}</div>
        </div>
        <div class="calendar_feedback">📝 Event Feedback</div>
      </div>`;
    }
}

modalClose.addEventListener("click", () => (modal.style.display = "none"));
window.addEventListener("click", (e) => {
    if (e.target === modal) modal.style.display = "none";
});

document.getElementById("calendar_prevMonth").addEventListener("click", () => {
    currentMonth--;
    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    renderCalendar();
});
document.getElementById("calendar_nextMonth").addEventListener("click", () => {
    currentMonth++;
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }
    renderCalendar();
});
eventFilter.addEventListener("change", renderCalendar);

function formatDate(dateStr) {
    const d = new Date(dateStr);
    return `${d.toLocaleString("default", {
        month: "long",
    })} ${d.getDate()}, ${d.getFullYear()}`;
}
function formatShortDate(dateStr) {
    const d = new Date(dateStr);
    return `${d.getMonth() + 1}/${d.getDate()}/${d.getFullYear()}`;
}

renderCalendar();

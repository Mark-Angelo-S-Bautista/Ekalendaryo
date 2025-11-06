// Laravel will inject this variable in Blade like:
// <script>const events = @json($events);</script>

// Format Laravel's events into the structure your calendar expects
const formattedEvents = (typeof events !== "undefined" ? events : []).map(
    (ev) => ({
        date: ev.date,
        title: ev.title,
        description: ev.description || "No description provided.",
        timeStart: ev.timeStart, // match PHP key
        timeEnd: ev.timeEnd, // match PHP key
        location: ev.location,
        sy: ev.sy, // match PHP key
        type: ev.type, // use the type sent by PHP
        organizer: ev.organizer, // use the organizer sent by PHP
    })
);

let currentMonth = new Date().getMonth(); // current month (0–11)
let currentYear = new Date().getFullYear();

const grid = document.getElementById("calendar_grid");
const monthYear = document.getElementById("calendar_monthYear");
const eventFilter = document.getElementById("calendar_eventFilter");

// 🎨 DEPARTMENT COLORS - Moved to global scope
const departmentColors = {
    bsis_act: "#0000FF",
    bsom: "#FF69B4", // Pink
    bsais: "#FFD700", // Yellow
    btvted: "#87CEFA", // Light Blue
    bsca: "#DAA520", // Gold
    default: "#28a745", // Green for all others
};

// 🗓️ RENDER CALENDAR
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

    // 🗓️ Add weekday labels (Su–Sa)
    const weekdays = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];
    weekdays.forEach((day) => {
        const dayHeader = document.createElement("div");
        dayHeader.classList.add("calendar_day-header");
        dayHeader.textContent = day;
        grid.appendChild(dayHeader);
    });

    const today = new Date();
    const todayStr = `${today.getFullYear()}-${String(
        today.getMonth() + 1
    ).padStart(2, "0")}-${String(today.getDate()).padStart(2, "0")}`;

    // Empty slots before the 1st day
    for (let i = 0; i < startDay; i++) {
        const empty = document.createElement("div");
        empty.classList.add("calendar_empty");
        grid.appendChild(empty);
    }

    // 🧭 Create each day cell
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

        if (date === todayStr) dayEl.classList.add("calendar_today");

        const filtered = formattedEvents.filter((ev) => ev.date === date);
        const filterVal = eventFilter.value;

        filtered.forEach((ev) => {
            if (filterVal === "all" || filterVal === ev.type) {
                const e = document.createElement("div");
                e.classList.add("calendar_event", `calendar_${ev.type}`);
                e.textContent = ev.title;
                const deptKey = ev.type.toLowerCase();
                e.style.backgroundColor =
                    departmentColors[deptKey] || departmentColors.default;
                e.style.color = "#fff";
                e.addEventListener("click", (event) => {
                    event.stopPropagation();
                    openEventDetail(ev);
                });
                dayEl.appendChild(e);
            }
        });

        dayEl.addEventListener("click", () => openDayModal(date));
        grid.appendChild(dayEl);
    }
}

// 🎯 MODAL LOGIC
const modal = document.getElementById("calendar_modal");
const modalTitle = document.getElementById("calendar_modal-title");
const modalBody = document.getElementById("calendar_modal-body");
const modalClose = document.getElementById("calendar_close");

// Helper: convert "HH:mm" (24-hour) to decimal
function timeStrToNumber(timeStr) {
    if (!timeStr) return 0;
    const [h, m] = timeStr.split(":").map(Number);
    return h + m / 60;
}

function openDayModal(date) {
    modal.style.display = "flex";
    modalTitle.textContent = formatDate(date);

    const dayEvents = formattedEvents.filter((ev) => ev.date === date);

    const startHour = 8;
    const endHour = 18;
    const slotInterval = 30; // minutes

    let timeSlotsHTML = "";
    const renderedEvents = new Set(); // Track which events we've already rendered

    for (let hour = startHour; hour < endHour; hour++) {
        for (let min = 0; min < 60; min += slotInterval) {
            const slotTimeNum = hour + min / 60;
            const timeLabel = `${String(hour).padStart(2, "0")}:${String(
                min
            ).padStart(2, "0")}`;

            // Find event covering this slot
            const eventForSlot = dayEvents.find((ev) => {
                const startNum = timeStrToNumber(ev.timeStart);
                const endNum = timeStrToNumber(ev.timeEnd);
                return slotTimeNum >= startNum && slotTimeNum < endNum;
            });

            if (eventForSlot) {
                // Only render the event title at its start time
                const eventStartNum = timeStrToNumber(eventForSlot.timeStart);
                const isStartSlot =
                    Math.abs(slotTimeNum - eventStartNum) < 0.01;

                const bgColor =
                    departmentColors[eventForSlot.type.toLowerCase()] ||
                    departmentColors.default;

                if (isStartSlot && !renderedEvents.has(eventForSlot.title)) {
                    renderedEvents.add(eventForSlot.title);
                    timeSlotsHTML += `
                        <div class='calendar_event-time' style='background-color: ${bgColor}; color:#fff; font-weight: bold; padding: 8px;'>
                            ${timeLabel} - ${eventForSlot.title}
                        </div>
                    `;
                } else {
                    // Continuation of the event (no title)
                    timeSlotsHTML += `
                        <div class='calendar_event-time' style='background-color: ${bgColor}; color:#fff; padding: 8px;'>
                            ${timeLabel}
                        </div>
                    `;
                }
            } else {
                // Empty slot
                timeSlotsHTML += `
                    <div class='calendar_noevent-time' style='background-color: #f5f5f5; color:#666; padding: 8px;'>
                        ${timeLabel}
                    </div>
                `;
            }
        }
    }

    modalBody.innerHTML = `
        <div class="calendar_noevent-list">
            ${timeSlotsHTML}
        </div>
    `;
}

function openEventDetail(eventData) {
    modal.style.display = "flex";
    modalTitle.textContent = formatDate(eventData.date);
    modalBody.innerHTML = `
    <div class="calendar_event-detail">
        <div class="calendar_badges">
            <div class="calendar_event-title">${eventData.title}</div>
            <span class="calendar_badge upcoming">upcoming</span>
        </div>
        <div class="calendar_event-description">
            ${eventData.description || "No description provided."}
        </div>
        <div class="calendar_event-info">
            <div>📅 ${formatShortDate(eventData.date)}</div>
            <div>⏰ ${eventData.timeStart} - ${eventData.timeEnd}</div>
            <div>📍 ${eventData.location}</div>
            <div>👤 ${eventData.organizer}</div>
            <div>${eventData.sy}</div>
        </div>
    </div>`;
}

modalClose.addEventListener("click", () => (modal.style.display = "none"));
window.addEventListener("click", (e) => {
    if (e.target === modal) modal.style.display = "none";
});

// 🔄 Navigation
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

// 📅 Helpers
function formatDate(dateStr) {
    const d = new Date(dateStr + "T00:00:00");
    return `${d.toLocaleString("default", {
        month: "long",
    })} ${d.getDate()}, ${d.getFullYear()}`;
}

function formatShortDate(dateStr) {
    const d = new Date(dateStr + "T00:00:00");
    return `${d.getMonth() + 1}/${d.getDate()}/${d.getFullYear()}`;
}

// 🏁 Initial render
renderCalendar();

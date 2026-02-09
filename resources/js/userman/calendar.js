// Ensure the script runs ONLY after the HTML is fully loaded and parsed.
document.addEventListener("DOMContentLoaded", () => {
    // --- NEW DATA LOADING LOGIC ---
    let events = []; // Default to an empty array
    const eventDataElement = document.getElementById("calendar-event-data");

    if (eventDataElement) {
        try {
            events = JSON.parse(eventDataElement.textContent);
            console.log("✅ Successfully loaded events:", events);
        } catch (e) {
            console.error("❌ Failed to parse event JSON:", e);
        }
    } else {
        console.error(
            "❌ Could not find <script id='calendar-event-data'> element!",
        );
    }

    const formattedEvents = events.map((ev) => ({
        date: ev.date,
        title: ev.title,
        description: ev.description || "No description provided.",
        moreDetails: ev.moreDetails || "No additional details.",
        timeStart: ev.timeStart,
        timeEnd: ev.timeEnd,
        location: ev.location,
        sy: ev.sy,
        type: ev.type,
        organizer: ev.organizer,
        targetYearLevels: ev.targetYearLevels,
        more_details: ev.more_details || "No additional details provided.",
        status: ev.status, // Already lowercase from DB
    }));

    console.log("=== Formatted Events ===", formattedEvents);

    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();

    const grid = document.getElementById("calendar_grid");
    const monthYear = document.getElementById("calendar_monthYear");
    const eventFilter = document.getElementById("calendar_eventFilter");

    const departmentColors = {
        bsis_act: "#0000FF",
        bsom: "#FF69B4",
        bsais: "#FFD700",
        btvted: "#87CEFA",
        bsca: "#DAA520",
        default: "#28a745",
    };

    // Helper to normalize status (optional but safe)
    function normalizeStatus(status) {
        return status ? status.toString().trim().toLowerCase() : "";
    }

    // 🗓️ Render Calendar
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

        const weekdays = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];
        weekdays.forEach((day) => {
            const dayHeader = document.createElement("div");
            dayHeader.classList.add("calendar_day-header");
            dayHeader.textContent = day;
            grid.appendChild(dayHeader);
        });

        const today = new Date();
        const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, "0")}-${String(today.getDate()).padStart(2, "0")}`;

        for (let i = 0; i < startDay; i++) {
            const empty = document.createElement("div");
            empty.classList.add("calendar_empty");
            grid.appendChild(empty);
        }

        for (let d = 1; d <= totalDays; d++) {
            const dayEl = document.createElement("div");
            dayEl.classList.add("calendar_day");

            const date = `${currentYear}-${String(currentMonth + 1).padStart(2, "0")}-${String(d).padStart(2, "0")}`;
            const num = document.createElement("div");
            num.classList.add("calendar_day-number");
            num.textContent = d;
            dayEl.appendChild(num);

            if (date === todayStr) dayEl.classList.add("calendar_today");

            const filtered = formattedEvents.filter((ev) => ev.date === date);
            const filterVal = eventFilter.value;

            filtered.forEach((ev) => {
                // 🚫 DO NOT RENDER CANCELLED EVENTS
                if (normalizeStatus(ev.status) === "cancelled") return;

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

    // 🎯 Modal Logic
    const modal = document.getElementById("calendar_modal");
    const modalTitle = document.getElementById("calendar_modal-title");
    const modalBody = document.getElementById("calendar_modal-body");
    const modalClose = document.getElementById("calendar_close");

    const moreDetailsModal = document.getElementById("moreDetailsModal");
    const moreDetailsContent = document.getElementById("moreDetailsContent");
    const moreDetailsClose = document.getElementById("closeMoreDetailsBtn");

    function timeStrToNumber(timeStr) {
        if (!timeStr) return 0;
        const [h, m] = timeStr.split(":").map(Number);
        return h + m / 60;
    }

    function openDayModal(date) {
    modal.style.display = "flex";

    const d = new Date(date + "T00:00:00");
    const weekdays = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
    const dayName = weekdays[d.getDay()];

    // For mobile, show the day name in the modal title
    modalTitle.textContent = `${formatDate(date)} (${dayName})`;

    const dayEvents = formattedEvents.filter(ev => ev.date === date);
    const startHour = 7, endHour = 18, slotInterval = 30;
    let timeSlotsHTML = "";
    const renderedEvents = new Set();

    function formatTime12Hour(hour, min) {
        const period = hour >= 12 ? "PM" : "AM";
        let hour12 = hour % 12;
        if (hour12 === 0) hour12 = 12;
        return `${hour12}:${String(min).padStart(2,"0")} ${period}`;
    }

    for(let hour=startHour; hour<endHour; hour++){
        for(let min=0; min<60; min+=slotInterval){
            const slotTimeNum = hour + min/60;
            const timeLabel = formatTime12Hour(hour, min);
            const eventForSlot = dayEvents.find(ev=>{
                const startNum = timeStrToNumber(ev.timeStart);
                const endNum = timeStrToNumber(ev.timeEnd);
                return slotTimeNum >= startNum && slotTimeNum < endNum;
            });

            if(eventForSlot){
                const eventStartNum = timeStrToNumber(eventForSlot.timeStart);
                const isStartSlot = Math.abs(slotTimeNum - eventStartNum) < 0.01;
                const bgColor = departmentColors[eventForSlot.type.toLowerCase()] || departmentColors.default;

                if(isStartSlot && !renderedEvents.has(eventForSlot.title)){
                    renderedEvents.add(eventForSlot.title);
                    timeSlotsHTML += `<div class='calendar_event-time' style='background-color:${bgColor}; color:#fff; font-weight:bold; padding:8px;'>${timeLabel} - ${eventForSlot.title}</div>`;
                } else {
                    timeSlotsHTML += `<div class='calendar_event-time' style='background-color:${bgColor}; color:#fff; padding:8px;'>${timeLabel}</div>`;
                }
            } else {
                timeSlotsHTML += `<div class='calendar_noevent-time' style='background-color:#f5f5f5; color:#666; padding:8px;'>${timeLabel}</div>`;
            }
        }
    }

    modalBody.innerHTML = `<div class="calendar_noevent-list">${timeSlotsHTML}</div>`;
}


    function openEventDetail(eventData) {
        modal.style.display = "flex";
        modalTitle.textContent = formatDate(eventData.date);

        let yearLevelsString = "All year levels";
        if (typeof eventData.targetYearLevels === "string") {
            try {
                const parsed = JSON.parse(eventData.targetYearLevels);
                if (Array.isArray(parsed) && parsed.length > 0)
                    yearLevelsString = parsed.join(", ");
            } catch (e) {
                yearLevelsString = "Error parsing year levels";
            }
        } else if (Array.isArray(eventData.targetYearLevels)) {
            if (eventData.targetYearLevels.length > 0)
                yearLevelsString = eventData.targetYearLevels.join(", ");
        } else if (
            eventData.targetYearLevels === null ||
            eventData.targetYearLevels === undefined
        ) {
            yearLevelsString = "Not specified (All year levels)";
        }

        // ✅ DYNAMIC STATUS BADGE
        const status = normalizeStatus(eventData.status);

        modalBody.innerHTML = `
        <div class="calendar_event-detail">
            <div class="calendar_badges">
                <div class="calendar_event-title">${eventData.title}</div>
                <span class="calendar_badge ${status}">
                    ${status.toUpperCase()}
                </span>
            </div>
            <div class="calendar_event-description">
                ${eventData.description || "No description provided."}
            </div>
            <div class="calendar_event-info">
                <div>📅 ${formatShortDate(eventData.date)}</div>
                <div>⏰ ${eventData.timeStart} - ${eventData.timeEnd}</div>
                <div>📍 ${eventData.location}</div>
                <div>👤 ${eventData.organizer}</div>
                <div>🎓 ${yearLevelsString}</div>
                <div>SY.${eventData.sy}</div>
            </div>
            <button id="viewMoreDetailsBtn" style="margin-top:10px; padding:8px 12px; background:#007bff; color:#fff; border:none; border-radius:5px; cursor:pointer;">
                👁️ View More Details
            </button>
        </div>`;

        const detailsBtn = document.getElementById("viewMoreDetailsBtn");
        if (detailsBtn)
            detailsBtn.addEventListener("click", () =>
                openMoreDetailsModal(eventData),
            );
    }

    function openMoreDetailsModal(eventData) {
        const modal = document.getElementById("moreDetailsModal");
        const content = document.getElementById("moreDetailsContent");
        const closeBtn = document.getElementById("userDetailsCloseBtn");

        content.innerHTML = `
        <h2>${eventData.title}</h2>
        <p>${eventData.moreDetails || "No additional details."}</p>
        `;
        modal.style.display = "flex";
        closeBtn.addEventListener(
            "click",
            () => (modal.style.display = "none"),
        );
    }

    // Close modals
    modalClose.addEventListener("click", () => (modal.style.display = "none"));
    if (moreDetailsClose)
        moreDetailsClose.addEventListener(
            "click",
            () => (moreDetailsModal.style.display = "none"),
        );

    window.addEventListener("click", (e) => {
        if (e.target === modal) modal.style.display = "none";
        if (e.target === moreDetailsModal)
            moreDetailsModal.style.display = "none";
    });

    // Navigation
    document
        .getElementById("calendar_prevMonth")
        .addEventListener("click", () => {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            renderCalendar();
        });

    document
        .getElementById("calendar_nextMonth")
        .addEventListener("click", () => {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            renderCalendar();
        });

    eventFilter.addEventListener("change", renderCalendar);

    function formatDate(dateStr) {
        const d = new Date(dateStr + "T00:00:00");
        return `${d.toLocaleString("default", { month: "long" })} ${d.getDate()}, ${d.getFullYear()}`;
    }

    function formatShortDate(dateStr) {
        const d = new Date(dateStr + "T00:00:00");
        return `${d.getMonth() + 1}/${d.getDate()}/${d.getFullYear()}`;
    }

    // Initial render
    renderCalendar();
});

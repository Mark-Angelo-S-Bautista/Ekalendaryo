<x-editorLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
        @vite(['resources/css/editor/manageEvents.css', 'resources/js/editor/manageEvents.js'])
    </head>

    <body>
        <div id="manage_event" class="tab-content">
            <div class="event-container">
                <header>
                    <h1>Event Management</h1>
                    <p>Create and manage events for your organization</p>
                    <button class="create-btn" id="openModalBtn">+ Create Event</button>
                </header>

                <!-- Create Event POP UP -->
                <div class="modal-overlay" id="modalOverlay">
                    <div class="modal">
                        <h2>Create New Event</h2>
                        <p>Fill in the event details to create a new event</p>

                        <div class="form-group">
                            <label>Event title</label>
                            <input type="text" placeholder="Event title">
                        </div>

                        <div class="form-group">
                            <label>Event description</label>
                            <textarea placeholder="Event description"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Date</label>
                            <input type="date">
                        </div>

                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="time">
                        </div>

                        <div class="form-group">
                            <label>End Time</label>
                            <input type="time">
                        </div>

                        <div class="form-group">
                            <label>Event location</label>
                            <input type="text" placeholder="Event location">
                        </div>

                        <div class="form-group">
                            <label>School Year</label>
                            <input type="text" placeholder="SY.2025-2026" disabled>
                            <div class="note">Format: SY.YYYY-YYYY</div>
                        </div>

                        <div class="form-group">
                            <label>Notification Settings</label>
                            <label>Date</label>
                            <input type="date">
                            <label>Time</label>
                            <input type="time">
                        </div>

                        <div class="form-group">
                            <label>Target Year Levels</label>
                            <p class="note">Select which year levels of students will receive notifications for this
                                event</p>

                            <div class="checkbox_select">
                                <div class="checkbox-inline">
                                    <input type="checkbox" id="select_all_create">
                                    <label for="select_all_create">Select All Year Levels</label>
                                </div>
                            </div>

                            <div class="checkbox-group">
                                <div class="checkbox-inline">
                                    <input type="checkbox" id="year1_create" class="syear">
                                    <label for="year1_create">1st Year</label>
                                </div>
                                <div class="checkbox-inline">
                                    <input type="checkbox" id="year2_create" class="syear">
                                    <label for="year2_create">2nd Year</label>
                                </div>
                                <div class="checkbox-inline">
                                    <input type="checkbox" id="year3_create" class="syear">
                                    <label for="year3_create">3rd Year</label>
                                </div>
                                <div class="checkbox-inline">
                                    <input type="checkbox" id="year4_create" class="syear">
                                    <label for="year4_create">4th Year</label>
                                </div>
                            </div>
                        </div>

                        <div class="button-group">
                            <button class="btn-cancel" id="closeModalBtn">Cancel</button>
                            <button class="btn-create">Create Event</button>
                        </div>
                    </div>
                </div>

                <!-- Edit Event POP UP -->
                <div class="modal-overlay" id="edit_modalOverlay">
                    <div class="modal">
                        <h2>Edit Event</h2>
                        <p>Update event details and settings</p>

                        <div class="form-group">
                            <label>Event title</label>
                            <input type="text" placeholder="Event title">
                        </div>

                        <div class="form-group">
                            <label>Event description</label>
                            <textarea placeholder="Event description"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Date</label>
                            <input type="date">
                        </div>

                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="time">
                        </div>

                        <div class="form-group">
                            <label>End Time</label>
                            <input type="time">
                        </div>

                        <div class="form-group">
                            <label>Event location</label>
                            <input type="text" placeholder="Event location">
                        </div>

                        <div class="form-group">
                            <label>School Year</label>
                            <input type="text" placeholder="SY.2025-2026" disabled>
                            <div class="note">Format: SY.YYYY-YYYY</div>
                        </div>

                        <div class="form-group">
                            <label>Notification Settings</label>
                            <label>Date</label>
                            <input type="date">
                            <label>Time</label>
                            <input type="time">
                        </div>

                        <div class="form-group">
                            <label>Target Year Levels</label>
                            <p class="note">Select which year levels of students will receive notifications for this
                                event</p>

                            <div class="checkbox_select">
                                <div class="checkbox-inline">
                                    <input type="checkbox" id="select_all_edit">
                                    <label for="select_all_edit">Select All Year Levels</label>
                                </div>
                            </div>

                            <div class="checkbox-group">
                                <div class="checkbox-inline">
                                    <input type="checkbox" id="year1_edit" class="syear">
                                    <label for="year1_edit">1st Year</label>
                                </div>
                                <div class="checkbox-inline">
                                    <input type="checkbox" id="year2_edit" class="syear">
                                    <label for="year2_edit">2nd Year</label>
                                </div>
                                <div class="checkbox-inline">
                                    <input type="checkbox" id="year3_edit" class="syear">
                                    <label for="year3_edit">3rd Year</label>
                                </div>
                                <div class="checkbox-inline">
                                    <input type="checkbox" id="year4_edit" class="syear">
                                    <label for="year4_edit">4th Year</label>
                                </div>
                            </div>
                        </div>

                        <div class="button-group">
                            <button class="btn-cancel" id="edit_closeModalBtn">Cancel</button>
                            <button class="btn-create">Create Event</button>
                        </div>
                    </div>
                </div>

                <!-- Sample Event Cards -->
                <div class="event-card">
                    <div class="event-header">
                        <h2>CS Department Meeting</h2>
                        <div class="tags">
                            <span class="tag department">department</span>
                            <span class="tag upcoming">upcoming</span>
                        </div>
                    </div>
                    <p>Monthly department meeting to discuss curriculum updates</p>
                    <div class="event-info">
                        <span>📅 9/1/2025</span>
                        <span>⏰ 14:00</span>
                        <span>📍 CS Conference Room</span>
                    </div>
                    <div class="actions">
                        <button class="edit" id="editModal_btn">✏️</button>
                        <button class="delete">🗑️</button>
                    </div>
                </div>

            </div>
        </div>
    </body>

    </html>
</x-editorLayout>

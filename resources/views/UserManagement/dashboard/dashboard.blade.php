@extends('components.usermanLayout')

@section('content')
    <div id="dashboard" class="tab-content active">
        <div class = "dashboard-container">
            <div class="content_head">
                <h1>Welcome back, admin!</h1>
                <p>Admin Dashboard</p>
            </div>

            <div class="stats">
                <div class="card">
                    <div class="card-icon"><i class="fa-solid fa-calendar-days"></i></div>
                    <div class="card-text">
                        <h2>Total Events</h2>
                        <p>10 visible to your role</p> <!--need back here-->
                    </div>
                </div>

                <div class="card">
                    <div class="card-icon"><i class="fa-solid fa-clock"></i></div>
                    <div class="card-text">
                        <h2>Upcoming</h2>
                        <p>7 events scheduled</p> <!--need back here-->
                    </div>
                </div>

                <div class="card">
                    <div class="card-icon"><i class="fa-solid fa-calendar-day"></i></div>
                    <div class="card-text">
                        <h2>Today</h2>
                        <p>0 events today</p> <!--need back here-->
                    </div>
                </div>

                <div class="card">
                    <div class="card-icon"><i class="fa-solid fa-star"></i></div>
                    <div class="card-text">
                        <h2>My Events</h2>
                        <p>1 event you organized</p> <!--need back here-->
                    </div>
                </div>

                <div class="card">
                    <div class="card-icon"><i class="fa-solid fa-users"></i></div>
                    <div class="card-text">
                        <h2>Total Users</h2>
                        <p>9 registered users</p> <!--need back here-->
                    </div>
                </div>
            </div>

            <!-- Upcoming Events -->
            <section>
                <h2>Upcoming Events</h2>
                <p style="color:#666; font-size:14px; margin-bottom:15px;">
                    Next 2 upcoming events
                </p>

                <div class="event"> <!--need backend and dynamic here-->
                    <div class="event-details">
                        <h3>CS Department Meeting</h3>
                        <p>5/12/2025 @ 14:00 - CS Conference Room</p>
                        <p>Monthly department meeting to discuss curriculum updates</p>
                        <div class="event-tags">
                            <span class="tag">Department</span>
                            <span class="tag">Upcoming</span>
                        </div>
                    </div>

                    <button class="btn-comment" onclick="openComment()"><i class="fa-regular fa-comment"></i>
                        Comments</button>
                </div>

                <div class="event">
                    <div class="event-details">
                        <h3>Basketball Tournament</h3>
                        <p>5/13/2025 @ 10:00 - School Gymnasium</p>
                        <p>Inter-department basketball tournament finals</p>
                        <div class="event-tags">
                            <span class="tag">Sports</span>
                            <span class="tag">Upcoming</span>
                        </div>
                    </div>
                    <button class="btn-comment" onclick="openComment()"><i class="fa-regular fa-comment"></i>
                        Comments</button>
                </div>

                <!-- POPUP COMMENT -->

                <div id="pop_comment" class="comment">

                    <div class="popup">
                        <div class="popup-header">
                            <div class="popup-title">Comments for CS Department Meeting</div>
                            <button class="close-button" onclick="closeComment()">×</button>
                        </div>
                        <div class="popup-content">
                            Share your thoughts, suggestions, or messages about this event
                        </div>
                        <div class="tags">
                            <div class="tag">department</div>
                            <div class="tag">upcoming</div>
                        </div>
                        <div class="details">
                            <div class="date">9/1/2025</div>
                            <div class="time">14:00</div>
                            <div class="location">CS Conference Room</div>
                        </div>
                        <div class="comment-section">
                            Comments & Messages
                            <p>No comments yet. Be the first to share your thoughts!</p>
                        </div>
                        <div class="add-comment">Add Your Comment</div>
                        <input type="text" class="comment-input"
                            placeholder="Share your thoughts, suggestions, or questions about this event...">
                        <button class="post-button"> Post Comment</button>
                    </div>

                </div>


            </section>
        </div>
    </div>
@endsection

<x-usermanLayout>
    <div class="container">
        <div class="users_header">
            <div class="title">User Management</div>
            <div class="users_header_buttons">
                <button class="users_btn">⬆ Import Users</button>
                <a href="{{ route('UserManagement.users', ['AddUser' => 1]) }}"class="users_btn add">＋ Add
                    User</a>
            </div>
        </div>

        <div class="users_cards">
            <div class="users_card active" data-role="All Users">
                <h2>{{ $totalUsers }}</h2>
                <p>All Users</p>
            </div>
            <div class="users_card" data-role="Department Head">
                <h2>{{ $userManagementCount }}</h2>
                <p>User Management</p>
            </div>
            <div class="users_card" data-role="Student Government Adviser">
                <h2>{{ $editorCount }}</h2>
                <p>Editor</p>
            </div>
            <div class="users_card" data-role="Sports and Cultural Adviser">
                <h2>{{ $viewerCount }}</h2>
                <p>Viewer</p>
            </div>
        </div>

        <div class="users_top_actions">
            <a href="{{ route('UserManagement.users', ['AddDepartment' => 1]) }}"class="users_add_department">＋ Add
                Department</a>
        </div>

        <div class="users_searchbar">
            <form action="{{ route('UserManagement.users') }}" method="GET" style="display: flex; gap: 0.5rem;">
                <input type="text" id="search" name="query" placeholder="Search users by username or email..."
                    value="{{ $query ?? '' }}">
                <button type="submit" class="search_btn">Search</button>

                @if (!empty($query))
                    <a href="{{ route('UserManagement.users') }}" class="search_clear_btn">Clear</a>
                @endif
            </form>
        </div>

        <div class="users_active_filter" id="users_active_filter">
            <span>Active filters:</span>
            <div class="users_filter_tag">
                <span>All Users</span>
                <button class="users_filter_close">&times;</button>
            </div>
        </div>

        <div class="users_table">
            <table id="userTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->userId }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->department }}</td>
                            <td>{{ $user->role }}</td>
                            <td>
                                <a href="{{ route('UserManagement.edit', $user->id) }}" class="edit-btn">✏️ Edit</a> |
                                <form action="{{ route('UserManagement.delete', $user->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-btn"
                                        onclick="return confirm('Are you sure you want to delete this user?')">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="users_modal" id="users_modal">
        <div class="users_modal_content">
            <div class="users_modal_header">
                <h2>Select Department</h2>
                <span class="users_modal_close" id="users_modal_close">&times;</span>
            </div>
            <p>Choose a department to filter students</p>
            <div class="users_modal_body" id="users_modal_body"></div>
        </div>
    </div>

    {{-- ADD USER MODAL --}}

    @if (request()->has('AddUser'))
        @include('UserManagement.users.addUser')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const overlay = document.getElementById('adduser_overlay');
                if (overlay) overlay.style.display = 'flex'; // show modal overlay

                // referenced by the Cancel button in the modal
                window.closeAddUserModal = function() {
                    if (overlay) overlay.style.display = 'none';
                    // remove the query param from the URL without reloading
                    history.replaceState(null, '', '{{ url()->current() }}');
                };

                // optionally: open modal without navigating (if user clicks the Add link)
                const addLink = document.querySelector('a.users_btn.add');
                if (addLink) {
                    addLink.addEventListener('click', function(e) {
                        // if href points to same page with ?AddUser=1, prevent navigation and show modal client-side
                        const href = addLink.getAttribute('href') || '';
                        if (href.includes('AddUser')) {
                            e.preventDefault();
                            if (overlay) overlay.style.display = 'flex';
                            history.replaceState(null, '', href);
                        }
                    });
                }
            });
        </script>
    @endif

    {{-- ADD DEPARTMENT --}}

    @if (request()->has('AddDepartment'))
        @include('UserManagement.users.addDepartment')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const deptOverlay = document.getElementById('adddept_overlay');
                if (deptOverlay) deptOverlay.style.display = 'flex';

                window.closeAddDeptModal = function() {
                    if (deptOverlay) deptOverlay.style.display = 'none';
                    history.replaceState(null, '', '{{ url()->current() }}');
                };

                // Optional: make Add Department button open modal client-side without page reload
                const deptLink = document.querySelector('.users_add_department');
                if (deptLink) {
                    deptLink.addEventListener('click', function(e) {
                        const href = deptLink.getAttribute('href') || '';
                        if (href.includes('AddDepartment')) {
                            e.preventDefault();
                            if (deptOverlay) deptOverlay.style.display = 'flex';
                            history.replaceState(null, '', href);
                        }
                    });
                }
            });
        </script>
    @endif

    @if (session('success'))
        <div id="toast" class="toast show">
            <p>{{ session('success') }}</p>
        </div>
    @endif
</x-usermanLayout>

@extends('components.usermanLayout')

@section('content')
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
            <button class="users_add_department" id="openAddDept">＋ Add Department</button>
        </div>

        <div class="users_searchbar">
            <input type="text" id="search" placeholder="Search users by username or email...">
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
                            <td>{{ $user->role }}</td>
                            <td>
                                {{-- <a href="{{ route('UserManagement.edit', $user->id) }}">Edit</a> |
                                <form action="{{ route('UserManagement.delete', $user->id) }}" method="POST"
                                    style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                                </form> --}}
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
    @if (session('success'))
        <div id="toast" class="toast show">
            <p>{{ session('success') }}</p>
        </div>
    @endif
@endsection

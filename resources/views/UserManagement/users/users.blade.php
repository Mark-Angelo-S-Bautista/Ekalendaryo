<x-usermanLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
        @vite(['resources/css/userman/usersTabPractice.css', 'resources/js/userman/usersTabPractice.js'])
    </head>

    <body>
        <div class="container">
            <div class="users_header">
                <div class="title">User Management</div>
                <div class="users_header_buttons">
                    <button id="openImportModal" class="users_btn">📥 Import Users</button>
                    <button class="users_btn add" id="openAddUser" onclick="openAddUserModal()">＋ Add User</button>
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
        </div>

        <div class="users_top_actions">
            <button class="users_add_department" id="openAddDept">＋ Add Department</button>
        </div>

        {{-- SEARCH BAR FEATURE --}}
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

        <div class="users_table">
            <table id="userTable">
                <thead>
                    <tr>
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
                            <td>
                                <span style="display:block; font-weight:bold;">{{ $user->title }}</span>
                                <span>{{ $user->name }}</span>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->department }}</td>
                            <td>{{ $user->role }}</td>
                            <td>
                                <a href="{{ route('UserManagement.edit', $user->id) }}" class="edit-btn">✏️ Edit</a>
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


        <!-- Add Department Modal -->
        <div class="adddept_modal" id="adddept_overlay">
            <div class="adddept_modal_content">
                <div class="adddept_modal_header">
                    <h2>Manage Departments</h2>
                    <span class="adddept_close" onclick="closeAddDeptModal()">&times;</span>
                </div>

                {{-- Add Department Form --}}
                <form id="addDepartmentForm" action="{{ route('UserManagement.adddepartment') }}">
                    @csrf
                    <label for="department_name">Add New Department</label>
                    <input type="text" id="department_name" name="department_name" placeholder="e.g. BSIT, BSA, etc."
                        required>

                    <div id="addDeptMessage" class="adddept_message"></div> {{-- Placeholder for error/success messages --}}

                    <div class="adddept_actions">
                        <button type="button" class="adddept_btn cancel" onclick="closeAddDeptModal()">Cancel</button>
                        <button type="submit" class="adddept_btn add">Add</button>
                    </div>
                </form>

                {{-- Existing Departments --}}
                <div class="adddept_list">
                    <h3>Existing Departments</h3>
                    @if ($departments->count() > 0)
                        <ul>
                            @foreach ($departments as $department)
                                <li>
                                    <span>{{ $department->department_name }}</span>
                                    <form action="{{ route('UserManagement.deletedepartment', $department->id) }}"
                                        method="POST" style="display:inline;"
                                        onsubmit="return confirm('Delete {{ $department->department_name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="delete-dept-btn">🗑️</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="no-dept">No departments found.</p>
                    @endif
                </div>
            </div>
        </div>



        <!-- Add User Modal -->
        <div class="adduser_overlay" id="adduser_overlay">
            <div class="adduser_modal">
                <h2>Add New User</h2>
                <form action="{{ route('UserManagement.adduser') }} " method="post">
                    @csrf
                    <div class="adduser_form-group">
                        <label class="adduser_label">Username</label>
                        <input type="text" id="name" name="name" class="adduser_input" required>
                    </div>

                    <div class="adduser_form-group">
                        <label class="adduser_label">Title</label>
                        <select id="title" name="title" class="adduser_select" required>
                            <option value="">Select a Title</option>
                            <option value="Student">Student</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Department Head">Department Head</option>
                            <option value="Offices">Offices</option>
                        </select>
                    </div>

                    <div class="adduser_form-group" id="office_name_field" style="display: none;">
                        <label class="adduser_label">Office Name</label>
                        <input type="text" id="office_name" name="office_name" class="adduser_input"
                            placeholder="Enter name of office">
                    </div>

                    <div class="adduser_form-group">
                        <label class="adduser_label">Student ID or Employee ID</label>
                        <input type="text" id="userId" name="userId" class="adduser_input"
                            placeholder="userId" required>
                    </div>

                    <div class="adduser_form-group">
                        <label class="adduser_label">Email</label>
                        <input type="email" id="email" name="email" class="adduser_input"
                            placeholder="user@gmail.com" required>
                    </div>

                    <div class="adduser_form-group">
                        <label class="adduser_label">Department</label>
                        <select id="department" name="department" class="adduser_select" required>
                            <option value="">Select a Department</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->department_name }}">{{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="adduser_form-group">
                        <label class="adduser_label">Year Level</label>
                        <select id="yearlevel" name="yearlevel" class="adduser_select">
                            <option value="">Select a Year Level</option>
                            <option value="1stYear">1st Year</option>
                            <option value="2ndYear">2nd Year</option>
                            <option value="3rdYear">3rd Year</option>
                            <option value="4thYear">4th Year</option>
                        </select>
                    </div>

                    <div class="adduser_form-group">
                        <label class="adduser_label">Section</label>
                        <input type="text" id="section" name="section" class="adduser_input"
                            placeholder="eg. A, B, C, D">
                    </div>

                    <div class="adduser_form-group">
                        <label class="adduser_label">Role</label>
                        <select id="role" name="role" class="adduser_select" required>
                            <option value="">Select a role</option>
                            <option value="Viewer">Viewer</option>
                            <option value="Editor">Editor</option>
                            <option value="UserManagement">User Management</option>
                        </select>
                    </div>

                    <div class="adduser_form-group">
                        <label class="adduser_label">Default Password</label>
                        <div class="adduser_password-box">
                            <span class="adduser_password-lock">🔒</span>
                            <input type="text" id="password" name="password" class="adduser_input"
                                value="password" readonly>
                        </div>
                    </div>

                    <div id="adduser_dynamic-fields"></div>

                    <div class="adduser_actions">
                        <div id="addUserMessage" class="adduser_message"></div> {{-- Placeholder for errors/success --}}
                        <button type="button"class="adduser_btn adduser_btn-cancel"
                            onclick="closeAddUserModal()">Cancel</button>
                        <button type="submit" class="adduser_btn adduser_btn-create">Create User</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Import Users Modal -->
        <form action="{{ route('UserManagement.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="import_modal" id="import_modal">
                <div class="import_modal_content">
                    <div class="import_modal_header">
                        <h2>Import Users from CSV</h2>
                        <span class="import_modal_close" id="import_close">&times;</span>
                    </div>

                    <div class="import_modal_body">
                        <a href="{{ asset('files/user_import_template.csv') }}" download
                            class="download_template_btn">⬇
                            Download
                            Template</a>
                        <p style="font-size: 13px; color:#333;">Download the template first, then fill it with user
                            data
                        </p>

                        <div class="import_file_input">
                            <label for="csv_file" id="file_label">Choose File: No file chosen</label><br>
                            <input type="file" id="csv_file" name="csv_file" accept=".csv" required
                                style="display:none;">
                        </div>
                    </div>

                    <div class="import_modal_actions">
                        <button type="button" class="import_btn cancel" id="import_cancel">Cancel</button>
                        <button type="submit" class="import_btn import">Import Users</button>
                    </div>
                </div>
            </div>
        </form>

        @if (session('success'))
            <div id="toast" class="toast show">
                <p>{{ session('success') }}</p>
            </div>
        @endif

    </body>

    </html>
</x-usermanLayout>

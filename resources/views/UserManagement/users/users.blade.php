<x-usermanLayout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Management</title>
        {{-- Ensure you have the correct file paths --}}
        @vite(['resources/css/userman/usersTabPractice.css', 'resources/js/userman/usersTabPractice.js'])
    </head>

    <body>
        <div class="container">
            <div class="users_header">
                <div class="title">Users Management</div>
                <div class="users_header_buttons">
                    <button id="openImportModal" class="users_btn">📥 Import Users</button>
                    <button class="users_btn add" id="openAddUser" onclick="openAddUserModal()">＋ Add User</button>
                </div>
            </div>

            {{-- 
                ✅ FIX: Updated cards to use the new Title-based counts from the controller:
                $studentCount, $facultyCount, $deptHeadCount, $officesCount
            --}}
            <div class="users_cards">
                <div class="users_card active" data-role="All Users">
                    <h2>{{ $totalUsers }}</h2>
                    <p>All Users</p>
                </div>
                {{-- Department Head Card --}}
                <div class="users_card" data-role="Department Head">
                    <h2>{{ $deptHeadCount }}</h2>
                    <p>Department Head</p>
                </div>
                {{-- Faculty Card (formerly Editor) --}}
                <div class="users_card" data-role="Faculty">
                    <h2>{{ $facultyCount }}</h2>
                    <p>Faculty</p>
                </div>
                {{-- Student Card (formerly Viewer) --}}
                <div class="users_card" data-role="Student">
                    <h2>{{ $studentCount }}</h2>
                    <p>Student</p>
                </div>
                {{-- Offices Card (Added as a replacement for the 4th card, assuming $officesCount exists) --}}
                <div class="users_card" data-role="Offices">
                    <h2>{{ $officesCount }}</h2>
                    <p>Offices</p>
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

        <div class="users_table scrollable">
            <table id="userTable">
                <thead>
                    <tr>
                        <th>Name / Title</th>
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
                                <span style="display:block; font-weight:bold;">
                                    @if ($user->department === 'OFFICES')
                                        {{ $user->office_name ?? 'N/A' }}
                                    @else
                                        {{ $user->title ?? 'N/A' }}
                                    @endif
                                </span>
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

        {{-- The rest of the modals (User Modal, Add Department Modal, Add User Modal, Import Users Modal) remain unchanged as their structure seems correct for their functions. --}}

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
                    <input type="text" id="department_name" name="department_name"
                        placeholder="e.g. BSIT, BSA, etc.">

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
                                    {{-- <form action="{{ route('UserManagement.deletedepartment', $department->id) }}"
                                        method="POST" style="display:inline;"
                                        onsubmit="return confirm('Delete {{ $department->department_name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="delete-dept-btn">🗑️</button>
                                    </form> --}}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="no-dept">No departments found.</p>
                    @endif
                </div>
            </div>
        </div>



        <div class="adduser_overlay" id="adduser_overlay">
            <div class="adduser_modal">
                <h2>Add New User</h2>
                <form action="{{ route('UserManagement.adduser') }}" method="POST" id="addUserForm">
                    @csrf

                    <div class="adduser_form-group">
                        <label class="adduser_label">Username</label>
                        <input type="text" id="name" name="name" class="adduser_input"
                            value="{{ old('name') }}">
                        <div class="error-text" id="error-name"></div>
                    </div>

                    <div class="adduser_form-group">
                        <label class="adduser_label">Title</label>
                        <select id="title" name="title" class="adduser_select">
                            <option value="">Select a Title</option>
                            <option value="Student" {{ old('title') == 'Student' ? 'selected' : '' }}>Student</option>
                            <option value="Faculty" {{ old('title') == 'Faculty' ? 'selected' : '' }}>Faculty</option>
                            <option value="Department Head" {{ old('title') == 'Department Head' ? 'selected' : '' }}>
                                Department Head</option>
                            <option value="Offices" {{ old('title') == 'Offices' ? 'selected' : '' }}>Offices</option>
                        </select>
                        <div class="error-text" id="error-title"></div>
                    </div>

                    <div class="adduser_form-group" id="office_name_field" style="display:none;">
                        <label class="adduser_label">Office Name</label>
                        <input type="text" id="office_name" name="office_name" class="adduser_input"
                            value="{{ old('office_name') }}">
                        <div class="error-text" id="error-office_name"></div>
                    </div>

                    <div class="adduser_form-group">
                        <label class="adduser_label">Student ID or Employee ID</label>
                        <input type="text" id="userId" name="userId" class="adduser_input"
                            value="{{ old('userId') }}">
                        <div class="error-text" id="error-userId"></div>
                    </div>

                    <div class="adduser_form-group">
                        <label class="adduser_label">Email</label>
                        <input type="email" id="email" name="email" class="adduser_input"
                            value="{{ old('email') }}">
                        <div class="error-text" id="error-email"></div>
                    </div>

                    <div class="adduser_form-group" id="department_field">
                        <label class="adduser_label">Department</label>
                        <select id="department" name="department" class="adduser_select">
                            <option value="">Select a Department</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->department_name }}"
                                    {{ old('department') == $dept->department_name ? 'selected' : '' }}>
                                    {{ $dept->department_name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="error-text" id="error-department"></div>
                    </div>

                    <div class="adduser_form-group">
                        <label class="adduser_label">Year Level</label>
                        <select id="yearlevel" name="yearlevel" class="adduser_select">
                            <option value="">Select a Year Level</option>
                            <option value="1stYear" {{ old('yearlevel') == '1stYear' ? 'selected' : '' }}>1st Year
                            </option>
                            <option value="2ndYear" {{ old('yearlevel') == '2ndYear' ? 'selected' : '' }}>2nd Year
                            </option>
                            <option value="3rdYear" {{ old('yearlevel') == '3rdYear' ? 'selected' : '' }}>3rd Year
                            </option>
                            <option value="4thYear" {{ old('yearlevel') == '4thYear' ? 'selected' : '' }}>4th Year
                            </option>
                        </select>
                        <div class="error-text" id="error-yearlevel"></div>
                    </div>

                    <div class="adduser_form-group">
                        <label class="adduser_label">Section</label>
                        <input type="text" id="section" name="section" class="adduser_input"
                            value="{{ old('section') }}">
                        <div class="error-text" id="error-section"></div>
                    </div>

                    <div class="adduser_form-group">
                        <label class="adduser_label">Role</label>
                        <select id="role" name="role" class="adduser_select adduser_role_static">
                            <option value="">Select a role</option>
                            <option value="Viewer" {{ old('role') == 'Viewer' ? 'selected' : '' }}>Viewer</option>
                            <option value="Editor" {{ old('role') == 'Editor' ? 'selected' : '' }}>Editor</option>
                            <option value="UserManagement" {{ old('role') == 'UserManagement' ? 'selected' : '' }}>
                                User Management</option>
                        </select>
                        <div class="error-text" id="error-role"></div>
                    </div>

                    <style>
                        .adduser_role_static {
                            pointer-events: none;
                            background-color: #f5f5f5;
                            cursor: not-allowed;
                            opacity: 0.7;
                        }
                    </style>

                    <div class="adduser_form-group">
                        <label class="adduser_label">Default Password</label>
                        <div class="adduser_password-box">
                            <span class="adduser_password-lock">🔒</span>
                            <input type="text" id="password" name="password" class="adduser_input"
                                value="password" readonly>
                        </div>
                    </div>

                    <div class="adduser_actions">
                        <div id="addUserMessage" class="adduser_message"></div>
                        <button type="button" class="adduser_btn adduser_btn-cancel"
                            onclick="closeAddUserModal()">Cancel</button>
                        <button type="submit" class="adduser_btn adduser_btn-create">Create User</button>
                    </div>
                </form>
            </div>
        </div>

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
                            class="download_template_btn">⬇ Download Template</a>
                        <p style="font-size: 13px; color:#333;">
                            Download the template first, then fill it with user data
                        </p>

                        <div class="import_file_input">
                            <label for="csv_file" id="file_label">Choose File: No file chosen</label><br>
                            <input type="file" id="csv_file" name="csv_file" accept=".csv"
                                style="display:none;">
                        </div>

                        <div id="import_errors_container" style="color:red; margin-top:10px;"></div>
                    </div>

                    <div class="import_modal_actions">
                        <button type="button" class="import_btn cancel" id="import_cancel">Cancel</button>
                        <button type="submit" class="import_btn import">Import Users</button>
                    </div>
                </div>
            </div>
        </form>
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const importModal = document.getElementById("import_modal");
                const openImportBtn = document.getElementById("openImportModal");
                const closeImportBtn = document.getElementById("import_close");
                const cancelImportBtn = document.getElementById("import_cancel");
                const csvInput = document.getElementById("csv_file");
                const fileLabel = document.getElementById("file_label");
                const errorContainer = document.getElementById("import_errors_container");

                // --- Open modal manually ---
                if (openImportBtn) {
                    openImportBtn.addEventListener("click", () => {
                        if (importModal) importModal.style.display = "flex";
                    });
                }

                // --- Close modal ---
                const closeModal = () => importModal.style.display = "none";

                if (closeImportBtn) closeImportBtn.addEventListener("click", closeModal);
                if (cancelImportBtn) cancelImportBtn.addEventListener("click", closeModal);

                window.addEventListener("click", (e) => {
                    if (e.target === importModal) closeModal();
                });

                // --- Show selected filename ---
                if (fileLabel && csvInput) {
                    fileLabel.addEventListener("click", () => csvInput.click());
                    csvInput.addEventListener("change", () => {
                        const fileName = csvInput.files.length ? csvInput.files[0].name : "No file chosen";
                        fileLabel.textContent = `Choose File: ${fileName}`;
                    });
                }

                // --- Automatically open modal if there are import errors ---
                const importErrors = @json(session('importErrors') ?? []);
                if (importErrors.length && importModal) {
                    importModal.style.display = "flex";

                    if (errorContainer) {
                        let html = "<h4>Rows skipped due to errors:</h4><ul>";
                        importErrors.forEach((error) => {
                            html += `<li>Row ${error.row}: ${error.errors.join(", ")}</li>`;
                        });
                        html += "</ul>";
                        errorContainer.innerHTML = html;
                    }
                }

                // --- Add User Modal logic (if it was defined in JS) ---
                const titleSelect = document.getElementById('title');
                const officeNameField = document.getElementById('office_name_field');
                const departmentField = document.getElementById('department_field');
                const roleSelect = document.getElementById('role');

                // Function to set role based on title
                const setRoleBasedOnTitle = (selectedTitle) => {
                    if (selectedTitle === 'Student' || selectedTitle === 'Faculty') {
                        roleSelect.value = 'Viewer';
                    } else if (selectedTitle === 'Department Head' || selectedTitle === 'Offices') {
                        roleSelect.value = 'Editor';
                    } else {
                        roleSelect.value = '';
                    }
                };

                if (titleSelect) {
                    // Initialize role on page load if title is already selected
                    if (titleSelect.value) {
                        setRoleBasedOnTitle(titleSelect.value);
                    }

                    titleSelect.addEventListener('change', function() {
                        const selectedTitle = this.value;

                        // Show/hide fields based on title
                        if (selectedTitle === 'Offices') {
                            officeNameField.style.display = 'block';
                            departmentField.style.display = 'none';
                        } else if (selectedTitle === 'Student') {
                            officeNameField.style.display = 'none';
                            departmentField.style.display = 'block';
                        } else {
                            officeNameField.style.display = 'none';
                            departmentField.style.display = 'none';
                        }

                        // Automatically set role based on title
                        setRoleBasedOnTitle(selectedTitle);
                    });
                }
            });
        </script>

        @if (session('success'))
            <div id="toast" class="toast show">
                <p>{{ session('success') }}</p>
            </div>
        @endif

    </body>

    </html>
</x-usermanLayout>

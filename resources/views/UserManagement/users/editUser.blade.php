<x-usermanLayout>

    <head>
        @vite(['resources/css/userman/usersTabPractice.css', 'resources/js/userman/usersTabPractice.js'])
    </head>

    <div class="edituser_wrapper">
        <h2>Edit User</h2>

        <form id="editUserForm" action="{{ route('UserManagement.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="edituser_form-group">
                <label class="edituser_label">Name:</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="edituser_input">
                <div class="error-text" id="error-name"></div>
            </div>

            <div class="edituser_form-group">
                <label class="edituser_label">Title:</label>
                <input type="text" name="title" value="{{ old('title', $user->title) }}" class="edituser_input">
                <div class="error-text" id="error-title"></div>
            </div>

            <div class="edituser_form-group">
                <label class="edituser_label">Student ID / Employee ID:</label>
                <input type="text" name="userId" value="{{ old('userId', $user->userId) }}" class="edituser_input">
                <div class="error-text" id="error-userId"></div>
            </div>

            <div class="edituser_form-group">
                <label class="edituser_label">Email:</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="edituser_input">
                <div class="error-text" id="error-email"></div>
            </div>

            <div class="edituser_form-group">
                <label class="edituser_label">Department:</label>
                <select name="department" class="edituser_select">
                    <option value="">Select Department</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->department_name }}"
                            {{ $user->department == $dept->department_name ? 'selected' : '' }}>
                            {{ $dept->department_name }}
                        </option>
                    @endforeach
                </select>
                <div class="error-text" id="error-department"></div>
            </div>

            <div class="edituser_form-group">
                <label class="edituser_label">Year Level:</label>
                <select name="yearlevel" class="edituser_select">
                    <option value="">Select Year Level</option>
                    <option value="1stYear" {{ $user->yearlevel == '1stYear' ? 'selected' : '' }}>1st Year</option>
                    <option value="2ndYear" {{ $user->yearlevel == '2ndYear' ? 'selected' : '' }}>2nd Year</option>
                    <option value="3rdYear" {{ $user->yearlevel == '3rdYear' ? 'selected' : '' }}>3rd Year</option>
                    <option value="4thYear" {{ $user->yearlevel == '4thYear' ? 'selected' : '' }}>4th Year</option>
                </select>
                <div class="error-text" id="error-yearlevel"></div>
            </div>

            <div class="edituser_form-group">
                <label class="edituser_label">Section:</label>
                <input type="text" name="section" value="{{ old('section', $user->section) }}"
                    class="edituser_input">
                <div class="error-text" id="error-section"></div>
            </div>

            <div class="edituser_form-group">
                <label class="edituser_label">Role:</label>
                <select name="role" class="edituser_select">
                    <option value="">Select Role</option>
                    <option value="Viewer" {{ $user->role == 'Viewer' ? 'selected' : '' }}>Viewer</option>
                    <option value="Editor" {{ $user->role == 'Editor' ? 'selected' : '' }}>Editor</option>
                    <option value="UserManagement" {{ $user->role == 'UserManagement' ? 'selected' : '' }}>User
                        Management</option>
                </select>
                <div class="error-text" id="error-role"></div>
            </div>

            <div class="edituser_form-group">
                <label class="edituser_label">Change Password:</label>
                <input type="password" name="password" class="edituser_input"
                    placeholder="Leave blank to keep current password">
                <div class="error-text" id="error-password"></div>
            </div>

            <div class="edituser_actions">
                <div id="editUserMessage" class="edituser_message"></div>
                <a href="{{ route('UserManagement.users') }}" class="edituser_btn edituser_btn-cancel">Cancel</a>
                <button type="submit" class="edituser_btn edituser_btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</x-usermanLayout>

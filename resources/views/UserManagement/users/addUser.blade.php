<!-- Add User Modal -->
<div class="adduser_overlay" id="adduser_overlay">
    <div class="adduser_modal">
        <h2>Add New User</h2>
        <form action="{{ route('UserManagement.adduser') }} " method="post">
            @csrf
            <div class="adduser_form-group">
                <label class="adduser_label">Username</label>
                <input type="text" id="name" name="name" class="adduser_input">
            </div>

            <div class="adduser_form-group">
                <label class="adduser_label">Student ID or Employee ID</label>
                <input type="text" id="userId" name="userId" class="adduser_input" placeholder="userId">
            </div>

            <div class="adduser_form-group">
                <label class="adduser_label">Email</label>
                <input type="email" id="email" name="email" class="adduser_input" placeholder="user@gmail.com">
            </div>

            <div class="adduser_form-group">
                <label class="adduser_label">Department</label>
                <select id="department" name="department" class="adduser_select" onchange="updateAddUserForm()">
                    <option value="">Select a Department</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->department_name }}">{{ $dept->department_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="adduser_form-group">
                <label class="adduser_label">Year Level</label>
                <select id="yearlevel" name="yearlevel" class="adduser_select" onchange="updateAddUserForm()">
                    <option value="">Select a Year Level</option>
                    <option value="1stYear">1st Year</option>
                    <option value="2ndYear">2nd Year</option>
                    <option value="3rdYear">3rd Year</option>
                    <option value="4thYear">4th Year</option>
                </select>
            </div>

            <div class="adduser_form-group">
                <label class="adduser_label">Section</label>
                <input type="text" id="section" name="section" class="adduser_input" placeholder="eg. A, B, C, D">
            </div>

            <div class="adduser_form-group">
                <label class="adduser_label">Role</label>
                <select id="role" name="role" class="adduser_select" onchange="updateAddUserForm()">
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
                    <input type="text" id="password" name="password" class="adduser_input" value="password"
                        readonly>
                </div>
            </div>

            <div id="adduser_dynamic-fields"></div>

            <div class="adduser_actions">
                <button type="button"class="adduser_btn adduser_btn-cancel"
                    onclick="closeAddUserModal()">Cancel</button>
                <button type="submit" class="adduser_btn adduser_btn-create">Create User</button>
            </div>
        </form>
    </div>
</div>

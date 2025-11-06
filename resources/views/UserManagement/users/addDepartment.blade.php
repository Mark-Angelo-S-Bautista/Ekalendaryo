<div class="adddept_modal" id="adddept_overlay">
    <div class="adddept_modal_content">
        <div class="adddept_modal_header">
            <h2>Add Department</h2>
            <span class="adddept_close" onclick="closeAddDeptModal()">&times;</span>
        </div>
        <form action="{{ route('UserManagement.adddepartment') }}" method="POST">
            @csrf
            <label for="department_name">Department Name</label>
            <input type="text" id="department_name" name="department_name" placeholder="e.g. BSIT, BSA, etc."
                required>

            <div class="adddept_actions">
                <button type="button" class="adddept_btn cancel" onclick="closeAddDeptModal()">Cancel</button>
                <button type="submit" class="adddept_btn add">Add</button>
            </div>
            @error('department_name')
                <div class="alert alert-danger mt-2">
                    {{ $message }}
                </div>
            @enderror
        </form>
    </div>
</div>

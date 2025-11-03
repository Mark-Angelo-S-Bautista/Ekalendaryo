@extends('components.usermanLayout')

@section('content')
    <!-- Add Department Modal -->
    <div class="adddept_modal" id="adddept_modal">
        <div class="adddept_modal_content">
            <div class="adddept_modal_header">
                <h2>Add New Department</h2>
                <span class="adddept_close" id="adddept_close">&times;</span>
            </div>
            <p>Create a new department that will be available for user assignment.</p>
            <label>Department Name</label>
            <input type="text" placeholder="e.g., COMPUTER SCIENCE, BUSINESS ADMINISTRATION">
            <small>Department names will be automatically converted to uppercase.</small>
            <div class="adddept_actions">
                <button class="adddept_btn cancel" id="adddept_cancel">Cancel</button>
                <button class="adddept_btn add">Add Department</button>
            </div>
        </div>
    </div>
@endsection

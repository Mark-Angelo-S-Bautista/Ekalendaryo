<x-loginLayout>
    <div class="modal-backdrop">
        <div class="modal-content">
            <h2>Change Password</h2>

            @error('password')
                <p class="error-message-forgot">{{ $message }}</p>
            @enderror

            <form action="{{ route('password.change') }}" method="POST">
                @csrf

                <div class="modal-input">
                    <input type="password" name="password" placeholder="New Password" required>
                </div>

                <div class="modal-input">
                    <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
                </div>

                <div class="modal-buttons">
                    <button type="submit" class="modal-btn reset-btn">Save Password</button>
                </div>

            </form>
        </div>
    </div>
</x-loginLayout>

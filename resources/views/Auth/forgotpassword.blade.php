<x-loginLayout>
    <div class="modal-backdrop">
        <div class="modal-content">
            <h2>Forgot Password</h2>
            <p>Enter your email to receive a password reset link.</p>
            @error('userId')
                <p class="error-message-forgot">{{ $message }}</p>
            @enderror
            <form action="{{ route('password.reset.request') }}" method="POST">
                @csrf
                <div class="modal-input">
                    <input type="text" name="userId" placeholder="Enter your Student ID or Employee ID" required>
                </div>
                <div class="modal-input">
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="modal-buttons">
                    {{-- Clicking Cancel removes the ?forgot=1 --}}
                    <a href="{{ route('Auth.login') }}" class="modal-btn cancel-btn">Cancel</a>
                    <button type="submit" class="modal-btn reset-btn">Send Request</button>
                </div>
            </form>
        </div>
</x-loginLayout>

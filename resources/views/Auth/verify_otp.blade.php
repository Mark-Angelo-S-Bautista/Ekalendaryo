<x-loginLayout>
    <div class="modal-backdrop">
        <div class="modal-content">
            <h2>Verify OTP</h2>
            <p>Enter the 6-digit code sent to your email.</p>

            @error('otp')
                <p class="error-message-forgot">{{ $message }}</p>
            @enderror

            @if (session('success'))
                <p class="success-message">{{ session('success') }}</p>
            @endif

            <form action="{{ route('password.otp.verify') }}" method="POST">
                @csrf
                <div class="modal-input">
                    <input type="text" name="otp" placeholder="Enter OTP" required maxlength="6">
                </div>

                <div class="modal-buttons">
                    <a href="{{ route('Auth.login') }}" class="modal-btn cancel-btn">Cancel</a>
                    <button type="submit" class="modal-btn reset-btn">Verify</button>
                </div>
            </form>
        </div>
    </div>
</x-loginLayout>

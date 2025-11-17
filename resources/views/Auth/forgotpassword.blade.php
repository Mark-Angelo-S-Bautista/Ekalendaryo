<x-loginLayout>
    <div class="modal-backdrop">
        <div class="modal-content">
            <h2>Forgot Password</h2>
            <p>Enter your ID Number to receive an OTP to your email.</p>

            @error('userId')
                <p class="error-message-forgot">{{ $message }}</p>
            @enderror

            @if (session('success'))
                <p class="success-message">{{ session('success') }}</p>
            @endif

            <form action="{{ route('password.otp.request') }}" method="POST">
                @csrf
                <div class="modal-input">
                    <input type="text" name="userId" placeholder="Enter your ID Number" required>
                </div>

                <div class="modal-buttons">
                    <a href="{{ route('Auth.login') }}" class="modal-btn cancel-btn">Cancel</a>
                    <button type="submit" class="modal-btn reset-btn">Send OTP</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // This function checks if the page is being loaded from the browser's bfcache.
        window.addEventListener('pageshow', function(event) {
            // persisted == true means the page was loaded from the bfcache
            if (event.persisted) {
                // Force a hard reload, which forces the browser to make a fresh server request.
                window.location.reload();
            }
        });
    </script>
</x-loginLayout>

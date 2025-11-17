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

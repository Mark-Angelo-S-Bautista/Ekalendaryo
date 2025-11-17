<x-loginLayout>
    <div class="container">
        <!-- Logo placeholder (replace this image with your own) -->
        <div class="logo">
            <img src="{{ asset('img/Main_logo.png') }}" alt="eKalendaryo Logo">
            <p class="title">Centralized Calendar of Activities and Notification System for School Events</p>
        </div>

        <!-- Sign In Box -->
        <div class="box">
            <h3>Sign In</h3>
            <form action="{{ route('login') }}" method="post">
                @csrf
                <div class="form-group">
                    <input type="text" id="userId" name="userId" placeholder="Enter your ID Number" required>

                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Enter your Password"
                            required>

                        <!-- 👁️ Emoji Toggle -->
                        <span id="password-toggle" class="password-toggle" style="cursor: pointer; user-select:none;">
                            👁️
                        </span>
                    </div>
                </div>
                @error('userId')
                    <p class="error-message">{{ $message }}</p>
                @enderror
                <button type="submit" class="btn">Sign In</button>
            </form>
            <div class="forget-btn">
                <a href="{{ route('Auth.login', ['forgot' => 1]) }}" class="forgot-password">Forgot Password?</a>
            </div>
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

        const passwordInput = document.getElementById("password");
        const toggleBtn = document.getElementById("password-toggle");

        toggleBtn.addEventListener("click", function() {
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleBtn.textContent = "👁️‍🗨️"; // open eye
            } else {
                passwordInput.type = "password";
                toggleBtn.textContent = "👁️"; // closed eye
            }
        });
    </script>
    {{-- Laravel-controlled Modal --}}
    @if (request()->has('forgot'))
        @include('Auth.forgotpassword')
    @endif
    @if (session('success'))
        <div id="toast" class="toast show">
            <p>{{ session('success') }}</p>
        </div>
    @endif
</x-loginLayout>

<x-loginLayout>
    <img src="{{ asset('img/BPC_BG1.png') }}">

    <div class="modal-backdrop">
        <div class="modal-content">
            <h2>Change Password Required</h2>
            <p>For your first login, you must set a new password before continuing.</p>

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <p class="error-message-forgot">{{ $error }}</p>
                @endforeach
            @endif

            <form action="{{ route('firstLogin.password.update') }}" method="POST">
                @csrf
                <div class="modal-input">
                    <div class="password-wrapper">
                        <input id="new_password" type="password" name="new_password" placeholder="New Password" required
                            minlength="8">
                        <span class="password-toggle" data-target="new_password"
                            style="cursor: pointer; user-select:none;">👁️</span>
                    </div>
                </div>
                <div class="modal-input">
                    <div class="password-wrapper">
                        <input id="new_password_confirmation" type="password" name="new_password_confirmation"
                            placeholder="Confirm New Password" required minlength="8">
                        <span class="password-toggle" data-target="new_password_confirmation"
                            style="cursor: pointer; user-select:none;">👁️</span>
                    </div>
                </div>

                <div class="modal-buttons">
                    <a href="{{ route('logout') }}" class="modal-btn cancel-btn"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Logout
                    </a>
                    <button type="submit" class="modal-btn reset-btn">Update Password</button>
                </div>
            </form>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.password-toggle').forEach(function(toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const input = document.getElementById(toggleBtn.dataset.target);
                if (!input) {
                    return;
                }

                if (input.type === 'password') {
                    input.type = 'text';
                    toggleBtn.textContent = '👁️‍🗨️';
                } else {
                    input.type = 'password';
                    toggleBtn.textContent = '👁️';
                }
            });
        });
    </script>
</x-loginLayout>

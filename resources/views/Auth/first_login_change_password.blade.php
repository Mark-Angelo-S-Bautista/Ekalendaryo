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
                            minlength="5">
                        <span class="password-toggle" data-target="new_password"
                            style="cursor: pointer; user-select:none;">👁️</span>
                    </div>
                    <p id="passwordPolicyHint" style="font-size: 0.9rem; margin-top: 6px; color: #4b5563;">
                        Password must be at least 5 characters, with 1 uppercase letter and 1 special character.
                    </p>
                    <p id="passwordPolicyStatus" style="font-size: 0.9rem; margin-top: 4px;"></p>
                </div>
                <div class="modal-input">
                    <div class="password-wrapper">
                        <input id="new_password_confirmation" type="password" name="new_password_confirmation"
                            placeholder="Confirm New Password" required minlength="5">
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
        const firstLoginPasswordInput = document.getElementById('new_password');
        const firstLoginPasswordStatus = document.getElementById('passwordPolicyStatus');
        const firstLoginPasswordForm = document.querySelector('form[action="{{ route('firstLogin.password.update') }}"]');

        const isPasswordPolicyValid = (value) => {
            const hasMinLength = value.length >= 5;
            const hasUppercase = /[A-Z]/.test(value);
            const hasSpecial = /[^A-Za-z0-9]/.test(value);
            return hasMinLength && hasUppercase && hasSpecial;
        };

        const updatePolicyStatus = () => {
            if (!firstLoginPasswordInput || !firstLoginPasswordStatus) {
                return true;
            }

            const value = firstLoginPasswordInput.value || '';
            if (!value.length) {
                firstLoginPasswordStatus.textContent = '';
                return false;
            }

            const valid = isPasswordPolicyValid(value);
            firstLoginPasswordStatus.textContent = valid ?
                'Password meets requirements.' :
                'Password does not meet requirements yet.';
            firstLoginPasswordStatus.style.color = valid ? '#15803d' : '#b91c1c';
            return valid;
        };

        if (firstLoginPasswordInput) {
            firstLoginPasswordInput.addEventListener('input', updatePolicyStatus);
        }

        if (firstLoginPasswordForm) {
            firstLoginPasswordForm.addEventListener('submit', function(e) {
                const isValid = updatePolicyStatus();
                if (!isValid) {
                    e.preventDefault();
                }
            });
        }

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

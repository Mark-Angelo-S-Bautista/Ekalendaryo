<x-loginLayout>
    <div class="modal-backdrop">
        <div class="modal-content">
            <h2>Change Password</h2>

            @error('password')
                <p class="error-message-forgot">{{ $message }}</p>
            @enderror

            <form action="{{ route('password.change') }}" method="POST">
                @csrf

                <!-- PASSWORD FIELD -->
                <div class="modal-input password-wrapper">
                    <input type="password" id="password" name="password" placeholder="New Password" required>
                    <span class="toggle-eye" onclick="togglePassword('password', this)">👁️</span>
                </div>

                <!-- CONFIRM PASSWORD FIELD -->
                <div class="modal-input password-wrapper">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        placeholder="Confirm Password" required>
                    <span class="toggle-eye" onclick="togglePassword('password_confirmation', this)">👁️</span>
                </div>

                <div class="modal-buttons">
                    <button type="submit" class="modal-btn reset-btn">Save Password</button>
                </div>

            </form>
        </div>
    </div>

    {{-- Fix back-forward browser cache --}}
    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        // Toggle show/hide password
        function togglePassword(fieldId, iconElement) {
            const input = document.getElementById(fieldId);

            if (input.type === "password") {
                input.type = "text";
                iconElement.textContent = "🙈"; // Change icon
            } else {
                input.type = "password";
                iconElement.textContent = "👁️"; // Change back to eye
            }
        }
    </script>

    <style>
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper input {
            width: 100%;
            padding-right: 40px;
            /* Space for eye icon */
        }

        .toggle-eye {
            position: absolute;
            right: 10px;
            cursor: pointer;
            font-size: 18px;
            user-select: none;
        }
    </style>
</x-loginLayout>

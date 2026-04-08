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
                    <input type="password" name="new_password" placeholder="New Password" required minlength="8">
                </div>
                <div class="modal-input">
                    <input type="password" name="new_password_confirmation" placeholder="Confirm New Password" required
                        minlength="8">
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
</x-loginLayout>

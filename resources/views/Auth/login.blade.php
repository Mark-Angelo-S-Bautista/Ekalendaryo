@extends('components.loginLayout')

@section('content')
    <div class="login-container">
        <header class="header">
            <i class="fas fa-calendar-alt calendar-icon"></i>
            <h1>eKalendaryo</h1>
            <p class="subtitle">Centralized Calendar Management System</p>
        </header>

        <div class="login-card">
            <h2>Sign In</h2>
            <form action="{{ route('login') }}" method="post">
                @csrf
                <div class="input-group">
                    <i class="fas fa-user icon"></i>
                    <input type="text" id="userId" name="userId" placeholder="Student ID or Employee ID"
                        value="{{ old('userId') }}" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <i id="password-toggle" class="fas fa-eye password-toggle"></i>
                </div>

                @error('userId')
                    <p class="error-message">{{ $message }}</p>
                @enderror

                <button type="submit" class="sign-in-btn">Sign In</button>
            </form>

            {{-- When clicked, adds ?forgot=1 to the URL --}}
            <a href="{{ route('Auth.login', ['forgot' => 1]) }}" class="forgot-password">Forgot Password?</a>
        </div>
    </div>

    {{-- Laravel-controlled Modal --}}
    @if (request()->has('forgot'))
        <div class="modal-backdrop">
            <div class="modal-content">
                <h2>Forgot Password</h2>
                <p>Enter your email to receive a password reset link.</p>
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
        </div>
    @endif
@endsection

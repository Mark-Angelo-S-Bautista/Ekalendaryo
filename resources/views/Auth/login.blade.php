@extends('components.loginLayout')

@section('content')
    <div class="container">
        <!-- Logo placeholder (replace this image with your own) -->
        <div class="logo">
            <img src="{{ asset('img/Main_logo.png') }}" alt="eKalendaryo Logo">
            <p class="title">Centralized Calendar of Activities and Notification System for School Events</p>
        </div>

        <!-- Sign In Box -->
        <div class="box">
            <h3>Sign In</h3>
            <p class=subtitle>Enter your credentials to access the system</p>
            <form action="{{ route('login') }}" method="post">
                @csrf
                <div class="form-group">
                    <input type="text" id="userId" name="userId" placeholder="Ex. MA22013875" required>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <i id="password-toggle" class="fas fa-eye password-toggle"></i>
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

    {{-- Laravel-controlled Modal --}}
    @if (request()->has('forgot'))
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
        </div>
    @endif
@endsection

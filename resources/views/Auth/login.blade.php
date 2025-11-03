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
        @include('Auth.forgotpassword')
    @endif
    @if (session('success'))
        <div id="toast" class="toast show">
            <p>{{ session('success') }}</p>
        </div>
    @endif
</x-loginLayout>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKalendaryo - Sign In</title>
    <link rel="stylesheet" href="loginStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    @vite(['resources/css/loginStyles.css', 'resources/js/app.js'])
</head>

<body>
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
                        required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <i class="fas fa-eye password-toggle"></i>
                </div>

                <button type="submit" class="sign-in-btn">Sign In</button>
            </form>

            <a href="#" class="forgot-password">Forgot Password?</a>
        </div>
    </div>
</body>

</html>

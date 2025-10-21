<!DOCTYPE html>
<html>

<head>
    <title>User Management System</title>
</head>

<body>

    <header>
        <h1>User Management</h1>
    </header>

    <nav>
        <a href="{{ route('UserManagement.dashboard') }}">Dashboard</a> |
        <a href="{{ route('UserManagement.calendar') }}">Calendar</a> |
        <a href="{{ route('UserManagement.profile') }}">Profile</a>
    </nav>

    <hr>

    <main>
        @yield('content')

        <form action="{{ route('logout') }}" method="post">
            @csrf
            <button type="submit" class="sign-in-btn">Logout</button>
        </form>
    </main>

</body>

</html>

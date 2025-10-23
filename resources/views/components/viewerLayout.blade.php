<!DOCTYPE html>
<html>

<head>
    <title>Viewer System</title>
</head>

<body>

    <header>
        <h1>Viewer</h1>
    </header>

    <nav>
        <a href="{{ route('Viewer.dashboard') }}">Dashboard</a> |
        <a href="{{ route('Viewer.calendar') }}">Calendar</a> |
        <a href="{{ route('Viewer.profile') }}">Profile</a>
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

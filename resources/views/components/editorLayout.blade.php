<!DOCTYPE html>
<html>

<head>
    <title>Editor System</title>
</head>

<body>

    <header>
        <h1>Editor</h1>
    </header>

    <nav>
        <a href="{{ route('Editor.dashboard') }}">Dashboard</a>
        <a href="{{ route('Editor.calendar') }}">Calendar</a>
        <a href="{{ route('Editor.profile') }}">Profile</a>
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

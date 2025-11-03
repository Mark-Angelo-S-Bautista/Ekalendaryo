<!DOCTYPE html>
<html>

<head>
    <title>eKalendaryo - Editor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/umastyle.css', 'resources/js/auth/scriptuserman.js'])
</head>
<header class="navbar">
    <div class="logo">
        <img src="{{ asset('img/Main_logo.png') }}" alt="eKalendaryo Logo">

        <div class="user-info">
            <span class="role">Editor</span>
        </div>

    </div>

    <form action="{{ route('UserManagement.logout') }}" method="post">
        @csrf
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</header>

<body>

    <div class="tab-buttons">
        <a href="{{ route('UserManagement.dashboard') }}"
            class="tab-link {{ request()->routeIs('UserManagement.dashboard') ? 'active' : '' }}">
            Dashboard
        </a>
        <a href="{{ route('UserManagement.calendar') }}"
            class="tab-link {{ request()->routeIs('UserManagement.calendar') ? 'active' : '' }}">
            Calendar
        </a>
        <a href="{{ route('UserManagement.users') }}"
            class="tab-link {{ request()->routeIs('UserManagement.users') ? 'active' : '' }}">
            Users
        </a>
        <a href="{{ route('UserManagement.activity_log') }}"
            class="tab-link {{ request()->routeIs('UserManagement.activity_log') ? 'active' : '' }}">
            Activity Log
        </a>
        <a href="{{ route('UserManagement.history') }}"
            class="tab-link {{ request()->routeIs('UserManagement.history') ? 'active' : '' }}">
            History
        </a>
        <a href="{{ route('UserManagement.archive') }}"
            class="tab-link {{ request()->routeIs('UserManagement.archive') ? 'active' : '' }}">
            Archive
        </a>
        <a href="{{ route('UserManagement.profile') }}"
            class="tab-link {{ request()->routeIs('UserManagement.profile') ? 'active' : '' }}">
            Profile
        </a>

    </div>

    <hr>

    <main>
        {{ $slot }}
    </main>

</body>

</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>eKalendaryo</title>
    @vite(['resources/css/editor/dashboard.css', 'resources/js/editor/dashboard.js'])
</head>

<header>
    <header class="header">
        <div class="logo">
            <img src="{{ asset('img/Main_logo.png') }}" alt="eKalendaryo Logo">
            <span>Editor</span>
        </div>
        <form action="{{ route('Editor.logout') }}" method="post">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </header>

    <nav class="navbar">
        <a href="{{ route('Editor.dashboard') }}"
            class="nav_item {{ request()->routeIs('Editor.dashboard') ? 'active' : '' }}">
            Dashboard
        </a>
        <a href="{{ route('Editor.calendar') }}"
            class="nav_item {{ request()->routeIs('Editor.calendar') ? 'active' : '' }}">
            Calendar
        </a>
        <a href="{{ route('Editor.index') }}" class="nav_item {{ request()->routeIs('Editor.index') ? 'active' : '' }}">
            Manage Events
        </a>
        <a href="{{ route('Editor.activity_log') }}"
            class="nav_item {{ request()->routeIs('Editor.activity_log') ? 'active' : '' }}">
            Activity Log
        </a>
        <a href="{{ route('Editor.history') }}"
            class="nav_item {{ request()->routeIs('Editor.history') ? 'active' : '' }}">
            History
        </a>
        <a href="{{ route('Editor.archive') }}"
            class="nav_item {{ request()->routeIs('Editor.archive') ? 'active' : '' }}">
            Archive
        </a>
        <a href="{{ route('Editor.profile') }}"
            class="nav_item {{ request()->routeIs('Editor.profile') ? 'active' : '' }}">
            Profile
        </a>

    </nav>

    <script>
        const navItems = document.querySelectorAll(".nav_item");
        const mainContent = document.getElementById("main_content");

        // Load default tab
        loadTab("Tabs/dashboard.html");

        navItems.forEach(item => {
            item.addEventListener("click", () => {
                // Highlight active tab
                navItems.forEach(i => i.classList.remove("active"));
                item.classList.add("active");

                // Load page content
                loadTab(item.dataset.page);
            });
        });
    </script>
</header>

<body>
    <script>
        // This function checks if the page is being loaded from the browser's bfcache.
        window.addEventListener('pageshow', function(event) {
            // persisted == true means the page was loaded from the bfcache
            if (event.persisted) {
                // Force a hard reload, which forces the browser to make a fresh server request.
                window.location.reload();
            }
        });
    </script>
    {{ $slot }}
</body>

</html>

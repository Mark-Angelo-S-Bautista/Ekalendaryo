<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>eKalendaryo</title>
    @vite(['resources/css/viewer/dashboard.css'])
</head>

<header>
    <header class="header">
        <div class="logo">
            <img src="{{ asset('img/Main_logo.png') }}" alt="eKalendaryo Logo">
            <span>Viewer</span>
        </div>
        <form action="{{ route('Viewer.logout') }}" method="post">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </header>

    <nav class="navbar">
        <a href="{{ route('Viewer.dashboard') }}"
            class="nav_item {{ request()->routeIs('Viewer.dashboard') ? 'active' : '' }}">
            Dashboard
        </a>
        <a href="{{ route('Viewer.calendar') }}"
            class="nav_item {{ request()->routeIs('Viewer.calendar') ? 'active' : '' }}">
            Calendar
        </a>
        <a href="{{ route('Viewer.notifications') }}"
            class="nav_item {{ request()->routeIs('Viewer.notifications') ? 'active' : '' }}">
            Notifications
        </a>
        <a href="{{ route('Viewer.history') }}"
            class="nav_item {{ request()->routeIs('Viewer.history') ? 'active' : '' }}">
            History
        </a>
        <a href="{{ route('Viewer.profile') }}"
            class="nav_item {{ request()->routeIs('Viewer.profile') ? 'active' : '' }}">
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

        // This function checks if the page is being loaded from the browser's bfcache.
        window.addEventListener('pageshow', function(event) {
            // persisted == true means the page was loaded from the bfcache
            if (event.persisted) {
                // Force a hard reload, which forces the browser to make a fresh server request.
                window.location.reload();
            }
        });
    </script>
</header>

<body>
    {{ $slot }}
</body>

</html>

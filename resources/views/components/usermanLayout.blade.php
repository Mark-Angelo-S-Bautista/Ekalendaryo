<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>eKalendaryo</title>
    @vite(['resources/css/userman/UserManDashboard.css', 'resources/js/userman/UserManDashboard.js'])
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/NEW_MAINLOGO.png') }}">
</head>

<header>
    <header class="header">
        <div class="logo">
            <img src="{{ asset('img/BPCLOGO.png') }}" alt="BPC Logo" style="width: 60px;">
            <img src="{{ asset('img/Main_logo.png') }}" alt="eKalendaryo Logo">
        </div>
        <form action="{{ route('UserManagement.logout') }}" method="post">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </header>

    <nav class="navbar">
        <a href="{{ route('UserManagement.dashboard') }}"
            class="nav_item {{ request()->routeIs('UserManagement.dashboard') ? 'active' : '' }}">
            Dashboard
        </a>
        <a href="{{ route('UserManagement.calendar') }}"
            class="nav_item {{ request()->routeIs('UserManagement.calendar') ? 'active' : '' }}">
            Calendar
        </a>
        <a href="{{ route('UserManagement.users') }}"
            class="nav_item {{ request()->routeIs('UserManagement.users') ? 'active' : '' }}">
            Users
        </a>
        {{-- <a href="{{ route('UserManagement.activity_log') }}"
            class="nav_item {{ request()->routeIs('UserManagement.activity_log') ? 'active' : '' }}">
            Activity Log
        </a>
        <a href="{{ route('UserManagement.history') }}"
            class="nav_item {{ request()->routeIs('UserManagement.history') ? 'active' : '' }}">
            History
        </a> --}}
        <a href="{{ route('UserManagement.archive') }}"
            class="nav_item {{ request()->routeIs('UserManagement.archive') ? 'active' : '' }}">
            Archive
        </a>
        <a href="{{ route('UserManagement.profile') }}"
            class="nav_item {{ request()->routeIs('UserManagement.profile') ? 'active' : '' }}">
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

<script>

      document.addEventListener("DOMContentLoaded", () => {
            const header = document.querySelector(".header");
            const navbar = document.querySelector(".navbar");

            // Create menu button
            const menuBtn = document.createElement("button");
            menuBtn.classList.add("menu-btn");
            menuBtn.innerHTML = "☰"; // hamburger icon
            header.prepend(menuBtn);

            // Toggle navbar on click
            menuBtn.addEventListener("click", () => {
                navbar.classList.toggle("active");
            });

            // Close navbar on mobile when clicking a nav item
            const navItems = document.querySelectorAll(".nav_item");
            navItems.forEach(item => {
                item.addEventListener("click", () => {
                    if (window.innerWidth <= 900) {
                        navbar.classList.remove("active");
                    }
                });
            });

            // Existing tab highlight/loadTab code
            navItems.forEach(item => {
                item.addEventListener("click", () => {
                    navItems.forEach(i => i.classList.remove("active"));
                    item.classList.add("active");

                    if (item.dataset.page) {
                        loadTab(item.dataset.page);
                    }
                });
            });

            // Reload if loaded from bfcache
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });
        });



</script>



</html>

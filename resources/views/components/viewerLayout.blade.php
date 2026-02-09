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
            <img src="{{ asset('img/BPCLOGO.png') }}" alt="BPC Logo" style="width: 60px;">
            <img src="{{ asset('img/Main_logo.png') }}" alt="eKalendaryo Logo">
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

            // Close navbar when clicking a nav item (mobile only)
            const navItems = document.querySelectorAll(".nav_item");
            navItems.forEach(item => {
                item.addEventListener("click", () => {
                    if (window.innerWidth <= 900) {
                        navbar.classList.remove("active");
                    }
                });
            });

            // OPTIONAL: your old navItem click code for tabs
            navItems.forEach(item => {
                item.addEventListener("click", () => {
                    navItems.forEach(i => i.classList.remove("active"));
                    item.classList.add("active");

                    // Load page content if using dynamic tabs
                    if (item.dataset.page) {
                        loadTab(item.dataset.page);
                    }
                });
            });

            // Force reload if loaded from bfcache
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });
        });
    </script>
</header>

<body>
    {{ $slot }}
</body>

</html>

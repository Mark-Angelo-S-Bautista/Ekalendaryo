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
        @php
            $notifUser = auth()->user();
            $notifUserTitle = strtolower($notifUser->title ?? '');
            $notifCount = 0;
            $lastViewed = $notifUser->notifications_last_viewed_at ?? null;

            if ($notifUser) {
                $notifEvents = \App\Models\Event::where(function ($query) use ($notifUser, $notifUserTitle) {
                    if ($notifUserTitle === 'faculty') {
                        $query->where(function ($q) use ($notifUser) {
                            $q->whereRaw('JSON_CONTAINS(target_faculty, ?)', [
                                json_encode((string) $notifUser->id),
                            ])->orWhere(function ($subQ) use ($notifUser) {
                                $subQ
                                    ->where('target_users', 'LIKE', '%Faculty%')
                                    ->where('department', $notifUser->department);
                            });
                        });
                    } elseif ($notifUserTitle === 'student' || $notifUserTitle === 'viewer') {
                        $query->where(function ($q) use ($notifUser) {
                            $q->where('department', $notifUser->department)->orWhereJsonContains(
                                'target_department',
                                $notifUser->department,
                            );
                        });
                    } else {
                        $query
                            ->where('department', $notifUser->department)
                            ->orWhereJsonContains('target_department', $notifUser->department);
                    }
                })->get();

                // Additional filtering for students
                if ($notifUserTitle === 'student' || $notifUserTitle === 'viewer') {
                    $userSection = $notifUser->section ? strtolower(trim($notifUser->section)) : null;
                    $userYearLevel = $notifUser->yearlevel
                        ? strtolower(str_replace(' ', '', $notifUser->yearlevel))
                        : null;

                    $notifEvents = $notifEvents->filter(function ($event) use ($userSection, $userYearLevel) {
                        $targetUsersNormalized = strtolower($event->target_users ?? '');
                        if (
                            str_contains($targetUsersNormalized, 'faculty') ||
                            str_contains($targetUsersNormalized, 'department head') ||
                            str_contains($targetUsersNormalized, 'offices')
                        ) {
                            return false;
                        }

                        $targetSections = is_string($event->target_sections)
                            ? json_decode($event->target_sections, true) ?? []
                            : $event->target_sections ?? [];

                        $targetYearLevels = is_string($event->target_year_levels)
                            ? json_decode($event->target_year_levels, true) ?? []
                            : $event->target_year_levels ?? [];

                        $sectionMatch =
                            empty($targetSections) ||
                            ($userSection &&
                                in_array($userSection, array_map('strtolower', array_map('trim', $targetSections))));

                        $yearLevelMatch =
                            empty($targetYearLevels) ||
                            ($userYearLevel &&
                                in_array(
                                    $userYearLevel,
                                    array_map(function ($lvl) {
                                        return strtolower(str_replace(' ', '', $lvl));
                                    }, $targetYearLevels),
                                ));

                        return $sectionMatch && $yearLevelMatch;
                    });
                }

                // Only count events created or updated after the last time user viewed notifications
                if ($lastViewed) {
                    $notifEvents = $notifEvents->filter(function ($event) use ($lastViewed) {
                        return $event->created_at > $lastViewed || $event->updated_at > $lastViewed;
                    });
                }

                $notifCount = $notifEvents->count();
            }
        @endphp
        <a href="{{ route('Viewer.notifications') }}"
            class="nav_item {{ request()->routeIs('Viewer.notifications') ? 'active' : '' }}"
            style="position: relative;">
            Notifications
            @if ($notifCount > 0)
                <span class="notif-badge">{{ $notifCount > 99 ? '99+' : $notifCount }}</span>
            @endif
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

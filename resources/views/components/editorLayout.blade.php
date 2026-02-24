<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>eKalendaryo</title>
    @vite(['resources/css/editor/dashboard.css', 'resources/js/editor/dashboard.js'])
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/NEW_MAINLOGO.png') }}">
</head>

<header>
    <header class="header">
        <div class="logo">
            <img src="{{ asset('img/BPCLOGO.png') }}" alt="BPC Logo" style="width: 60px;">
            <img src="{{ asset('img/Main_logo.png') }}" alt="eKalendaryo Logo">
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
        @php
            $notifUser = auth()->user();
            $notifUserTitle = strtolower($notifUser->title ?? '');
            $notifCount = 0;
            $lastViewed = $notifUser->notifications_last_viewed_at;

            if ($notifUser) {
                $notifEvents = \App\Models\Event::where(function ($query) use ($notifUser, $notifUserTitle) {
                    if ($notifUserTitle === 'faculty') {
                        $query->whereRaw('JSON_CONTAINS(target_faculty, ?)', [json_encode((string) $notifUser->id)]);
                    } elseif ($notifUserTitle === 'student' || $notifUserTitle === 'viewer') {
                        $query->where(function ($q) use ($notifUser) {
                            $q->where('department', $notifUser->department)->orWhereJsonContains(
                                'target_department',
                                $notifUser->department,
                            );
                        });
                    } else {
                        $query->where(function ($q) use ($notifUser) {
                            $q->whereRaw('JSON_CONTAINS(target_faculty, ?)', [
                                json_encode((string) $notifUser->id),
                            ])->orWhere(function ($subQ) use ($notifUser) {
                                $subQ
                                    ->where('target_users', 'LIKE', '%' . $notifUser->title . '%')
                                    ->orWhere('target_users', $notifUser->title);
                            });
                        });
                    }
                })->get();

                // Additional filtering for students
                if ($notifUserTitle === 'student' || $notifUserTitle === 'viewer') {
                    $userSection = $notifUser->section ? strtolower(trim($notifUser->section)) : null;
                    $userYearLevel = $notifUser->yearlevel
                        ? strtolower(str_replace(' ', '', $notifUser->yearlevel))
                        : null;

                    $notifEvents = $notifEvents->filter(function ($event) use ($userSection, $userYearLevel) {
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

                // Additional filtering for Department Heads and Office users
                if (!in_array($notifUserTitle, ['faculty', 'student', 'viewer'])) {
                    $notifEvents = $notifEvents->filter(function ($event) use ($notifUser) {
                        $targetFaculty = is_string($event->target_faculty)
                            ? json_decode($event->target_faculty, true) ?? []
                            : $event->target_faculty ?? [];

                        if (is_array($targetFaculty) && in_array($notifUser->id, $targetFaculty)) {
                            return true;
                        }

                        if ($notifUser->department === 'OFFICES' || $notifUser->title === 'Offices') {
                            return false;
                        }

                        $targetUsers = $event->target_users ?? '';
                        if (!empty($targetUsers)) {
                            if ($notifUser->title === 'Department Head') {
                                if ($targetUsers === 'Department Heads') {
                                    $targetDepartments = $event->target_department;
                                    if (is_string($targetDepartments)) {
                                        $targetDepartments = json_decode($targetDepartments, true) ?? [];
                                    }
                                    if (!is_array($targetDepartments)) {
                                        $targetDepartments = [];
                                    }

                                    $normalizedTargetDepts = array_map(
                                        fn($d) => strtoupper(trim($d)),
                                        $targetDepartments,
                                    );
                                    $userDeptNormalized = strtoupper(trim($notifUser->department));

                                    if (
                                        in_array('All', $targetDepartments) ||
                                        in_array($userDeptNormalized, $normalizedTargetDepts)
                                    ) {
                                        return true;
                                    }
                                    return false;
                                }
                                if ($targetUsers === 'Faculty' && $event->department === $notifUser->department) {
                                    return true;
                                }
                                return false;
                            }

                            if (
                                $targetUsers === $notifUser->title ||
                                stripos($targetUsers, $notifUser->title) !== false
                            ) {
                                return true;
                            }
                        }

                        return false;
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
        <a href="{{ route('Editor.notifications') }}"
            class="nav_item {{ request()->routeIs('Editor.notifications') ? 'active' : '' }}"
            style="position: relative;">
            Notifications
            @if ($notifCount > 0)
                <span class="notif-badge">{{ $notifCount > 99 ? '99+' : $notifCount }}</span>
            @endif
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

            // Tab highlighting / loadTab logic
            navItems.forEach(item => {
                item.addEventListener("click", () => {
                    navItems.forEach(i => i.classList.remove("active"));
                    item.classList.add("active");
                    if (item.dataset.page) {
                        loadTab(item.dataset.page);
                    }
                });
            });

            // Reload if page loaded from bfcache
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    window.location.reload();
                }
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

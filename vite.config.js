import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // JS FILES
                "resources/js/auth/login.js",
                "resources/js/auth/scriptuserman.js",

                "resources/js/editor/archive.js",
                "resources/js/editor/dashboard.js",
                "resources/js/editor/history.js",
                "resources/js/editor/manageEvents.js",
                "resources/js/editor/profile.js",
                "resources/js/editor/activity_log.js",

                "resources/js/userman/activityLog.js",
                "resources/js/userman/archive.js",
                "resources/js/userman/calendar.js",
                "resources/js/userman/history.js",
                "resources/js/userman/UserManDashboard.js",
                "resources/js/userman/UserManProfile.js",
                "resources/js/userman/usersTabPractice.js",

                "resources/js/viewer/history.js",
                "resources/js/viewer/profile.js",

                // CSS FILES
                "resources/css/loginStyles.css",
                "resources/css/umastyle.css",

                "resources/css/editor/activity_log.css",
                "resources/css/editor/archive.css",
                "resources/css/editor/dashboard.css",
                "resources/css/editor/editEvents.css",
                "resources/css/editor/history.css",
                "resources/css/editor/manageEvents.css",
                "resources/css/editor/profile.css",

                "resources/css/userman/activityLog.css",
                "resources/css/userman/app.css",
                "resources/css/userman/archive.css",
                "resources/css/userman/calendar.css",
                "resources/css/userman/history.css",
                "resources/css/userman/UserManDashboard.css",
                "resources/css/userman/UserManProfile.css",
                "resources/css/userman/usersTabPractice.css",

                "resources/css/viewer/dashboard.css",
                "resources/css/viewer/history.css",
                "resources/css/viewer/notifications.css",
                "resources/css/viewer/profile.css",
                "resources/css/viewer/viewerdashboard.css",
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});

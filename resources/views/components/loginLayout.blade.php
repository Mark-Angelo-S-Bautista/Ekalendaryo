<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKalendaryo - Sign In</title>
    @vite(['resources/css/loginStyles.css', 'resources/js/auth/login.js'])

</head>
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

<body>
    {{ $slot }}
</body>

</html>

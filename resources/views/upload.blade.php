<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>UPLOAD</title>

        <script src="https://cdn.jsdelivr.net/npm/@mux/mux-uploader"></script>

        <!-- Fonts -->
    </head>
    <body class="font-sans antialiased dark:bg-black dark:text-white/50">
        UPLOAD

        <mux-uploader endpoint="https://httpbin.org/put"></mux-uploader>

    </body>
</html>

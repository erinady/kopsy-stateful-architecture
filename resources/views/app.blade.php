<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @vite('resources/js/app.js')
        @include('sweetalert2::index')
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>

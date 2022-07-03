<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'Magnifier Media Manager' }}</title>
    @vite([
        'resources/vendor/Magnifier/js/app.js',
        'resources/vendor/Magnifier/js/vue.js',
        'resources/vendor/Magnifier/sass/app.scss',
    ])
    @stack('css')
</head>

<body>
    <div id="app" >
        <x-magnifier::layout.flash />
        {{ $slot }}
    </div>
    @stack('js')
</body>

</html>

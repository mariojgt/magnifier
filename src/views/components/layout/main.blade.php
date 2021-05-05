<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'Magnifier Media Manager' }}</title>
    <link href="{{ asset('vendor/Magnifier/css/app.css') }}" rel="stylesheet">
    @stack('css')
</head>

<body>
    <div id="app">
        <div class="content">
            <x-magnifier::layout.flash />
            {{ $slot }}
        </div>
    </div>

    <script src="{{ asset('vendor/Magnifier/js/app.js') }}"></script>
    <script src="{{ asset('vendor/Magnifier/js/vue.js') }}"></script>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
            });
    </script>

    @stack('js')
</body>

</html>

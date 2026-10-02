<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) — {{ config('homecare.hospital_name') }}</title>
    <meta name="description" content="@yield('description', 'Layanan kesehatan di rumah dari tenaga profesional '.config('homecare.hospital_name').'. Pesan homecare dengan mudah.')">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏥</text></svg>">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Penanda pilihan pada kartu radio/checkbox: variant peer-checked tidak
           menjangkau elemen yang bersarang di dalam, jadi pakai :has(). */
        .choice-dot svg { opacity: 0; }
        label:has(> input:checked) .choice-dot { background-color: #0d9488; border-color: #0d9488; }
        label:has(> input:checked) .choice-dot svg { opacity: 1; }
        label:has(> input:checked) .choice-name { color: #0f766e; font-weight: 800; }
    </style>
    @stack('head')
</head>
<body class="h-full flex flex-col">
    @yield('body')
    <x-flash />
    @stack('scripts')
</body>
</html>

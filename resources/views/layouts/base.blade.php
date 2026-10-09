<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#285fce">
    <meta name="color-scheme" content="light">
    <title>@yield('title', config('app.name')) — {{ config('homecare.hospital_name') }}</title>
    <meta name="description" content="@yield('description', 'Layanan kesehatan di rumah dari tenaga profesional '.config('homecare.hospital_name').'. Pesan homecare dengan mudah, cukup ketik kebutuhan Anda.')">
    <meta property="og:title" content="@yield('title', config('app.name'))">
    <meta property="og:description" content="Layanan homecare resmi {{ config('homecare.hospital_name') }}.">
    <meta property="og:type" content="website">
    <link rel="icon" href="{{ asset(config('homecare.logo_path')) }}" type="image/svg+xml">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Penanda pilihan pada kartu radio/checkbox: variant peer-checked tidak
           menjangkau elemen yang bersarang di dalam, jadi pakai :has(). */
        .choice-dot svg { opacity: 0; }
        label:has(> input:checked) .choice-dot { background-color: #285fce; border-color: #285fce; }
        label:has(> input:checked) .choice-dot svg { opacity: 1; }
        label:has(> input:checked) .choice-name { color: #1d4ed8; font-weight: 800; }
    </style>
    @stack('head')
</head>
<body class="flex h-full flex-col bg-slate-50 font-sans text-clinic-900 antialiased">
    @yield('body')
    <x-flash />
    @stack('scripts')
</body>
</html>

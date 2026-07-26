<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Inertia drives title/og/description per page via SeoHead component --}}
    {{-- These are fallback defaults shown before React hydrates --}}
    <title inertia>{{ config('app.name', 'ChopChop Craft') }}</title>
    <meta name="description" content="Handcrafted chopping boards made in Kenya. Acacia, Walnut, Teak, Olive and more. Shop online with M-Pesa, Stripe or PayPal.">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    {{-- Favicon set --}}
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">

    {{-- Default OG fallback (SeoHead overrides these per page) --}}
    <meta property="og:site_name" content="{{ config('app.name', 'ChopChop Craft') }}">
    <meta property="og:locale" content="en_KE">

    {{-- Theme colour (used by mobile browsers for UI chrome) --}}
    <meta name="theme-color" content="#292524">

    {{-- Ziggy routes (must be before Vite scripts) --}}
    @routes

    @vite(['resources/js/app.tsx',])
    @inertiaHead
</head>

<body class="font-sans antialiased bg-stone-50 text-stone-900">
    @inertia
</body>

</html>
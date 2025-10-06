@php
// Este template Blade é apenas um wrapper para inicializar o Inertia com uma página de erro.
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Example App') }}</title>
    @vite(['resources/css/app.css', 'resources/js/inertia.js'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>

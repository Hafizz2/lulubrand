<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-900 text-gray-100">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <title>LULU Admin Console</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
    @vite(['resources/js/admin.jsx'])
    @inertiaHead
  </head>
  <body class="h-full antialiased bg-gray-900 font-sans selection:bg-rose-500 selection:text-white">
    @inertia
  </body>
</html>

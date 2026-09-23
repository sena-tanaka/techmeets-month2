<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                @isset($slot)
                    {{-- Breeze・会員制ブログ（<x-app-layout> を使うビュー） --}}
                    {{ $slot }}
                @else
                    {{-- 商品管理（@extends('layouts.app') を使うビュー） --}}
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        @if (session('success'))
                            <p class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</p>
                        @endif

                        @yield('content')
                    </div>
                @endisset
            </main>
        </div>
    </body>
</html>

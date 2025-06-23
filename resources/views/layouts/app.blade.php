<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Admin MomyButuh')</title>

    {{-- CDN Tailwind & Konfigurasi --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: { extend: { colors: { primary: { 50: '#fff1f2', 100: '#ffe4e6', 200: '#fecdd3', 300: '#fda4af', 400: '#fb7185', 500: '#f43f5e', 600: '#e11d48', 700: '#be123c', 800: '#9f1239', 900: '#881337', 950: '#4c0519' }}}}
      }
    </script>
    
    {{-- CDN SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    {{-- CDN Flowbite untuk interaktivitas komponen (dropdown, sidebar mobile) --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-50">

    {{-- Memuat Navbar Mobile --}}
    @include('layouts.partials.navbar')

    {{-- Memuat Sidebar --}}
    @include('layouts.partials.sidebar')

    {{-- Area Konten Utama --}}
    <div class="p-4 sm:ml-64">
        <div class="mt-14 sm:mt-0">
            {{-- Tombol Logout dan Info User di pojok kanan atas --}}
            <div class="flex justify-end mb-4">
                <button id="dropdown-user-button" data-dropdown-toggle="dropdown-user" class="flex text-sm bg-gray-200 rounded-full focus:ring-4 focus:ring-gray-300 p-1">
                    <span class="sr-only">Open user menu</span>
                    <div class="w-8 h-8 rounded-full bg-primary-500 text-white flex items-center justify-center font-bold">
                        {{-- Inisial nama user --}}
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                </button>
                <div id="dropdown-user" class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow-lg">
                    <div class="px-4 py-3">
                        <span class="block text-sm text-gray-900">{{ Auth::user()->name }}</span>
                        <span class="block text-sm text-gray-500 truncate">{{ Auth::user()->email }}</span>
                    </div>
                    <ul class="py-2" aria-labelledby="dropdown-user-button">
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Di sini konten dari setiap halaman akan dimuat --}}
            @yield('content')
        </div>
    </div>

    {{-- Script Flowbite & SweetAlert --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <script>
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                position: 'center',
                showConfirmButton: false,
                timer: 2000
            });
        @endif
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                position: 'center',
                confirmButtonColor: '#e11d48'
            });
        @endif
    </script>
</body>
</html>
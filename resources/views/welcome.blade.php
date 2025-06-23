{{-- Menggunakan layout utama yang sudah kita buat --}}
@extends('layouts.app')

{{-- Mendefinisikan judul halaman --}}
@section('title', 'Dashboard')

{{-- Mengisi konten halaman --}}
@section('content')
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow">
        <h1 class="text-2xl font-bold text-gray-900">Selamat Datang di Panel Admin MomyButuh</h1>
        <p class="mt-2 text-gray-600">Fondasi proyek Anda sudah siap!</p>

        <div class="mt-6">
            {{-- Contoh Tombol dengan Warna Primary (Pink) --}}
            <a href="{{ route('test.notification') }}" 
               class="px-4 py-2 font-semibold text-white bg-primary-500 rounded-lg shadow-md hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-opacity-75">
               Tampilkan Notifikasi Berhasil
            </a>
        </div>
    </div>
@endsection
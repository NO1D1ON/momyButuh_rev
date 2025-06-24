@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    {{-- Kontainer luar disederhanakan, border putus-putus dihilangkan --}}
    <div class="p-4">
        {{-- Card ucapan selamat datang dengan latar belakang pink pudar --}}
        <div class="p-6 bg-[#ffdeef] rounded-xl shadow-md">
            <h1 class="text-2xl font-bold text-gray-800">
                Dashboard Admin
            </h1>
            <p class="mt-2 text-gray-600">
                Selamat datang kembali, {{ Auth::user()->name }}!
            </p>
        </div>
    </div>
@endsection
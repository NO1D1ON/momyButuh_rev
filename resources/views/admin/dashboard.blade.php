@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700">
        <div class="p-6 bg-white rounded-lg shadow">
            <h1 class="text-2xl font-bold text-gray-900">
                Dashboard Admin
            </h1>
            <p class="mt-2 text-gray-600">
                Selamat datang kembali, {{ Auth::user()->name }}!
            </p>
        </div>
    </div>
@endsection
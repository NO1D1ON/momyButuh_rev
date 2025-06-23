@extends('layouts.app')

@section('title', 'Tambah Babysitter')

@section('content')
<div class="p-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Tambah Babysitter Baru</h1>

    <div class="p-6 bg-white rounded-lg shadow">
        <form action="{{ route('babysitters.store') }}" method="POST">
            @csrf
            {{-- Sertakan _form.blade.php di sini --}}
            @include('admin.babysitters._form')
        </form>
    </div>
</div>
@endsection
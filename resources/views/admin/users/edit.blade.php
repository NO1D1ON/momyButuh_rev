@extends('layouts.app')

@section('title', 'Edit Pengguna')

@section('content')
<div class="p-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Edit Pengguna: {{ $user->name }}</h1>

    <div class="p-6 bg-white rounded-lg shadow">
        @if ($errors->any())
            <div class="p-4 mb-4 text-sm text-red-800 bg-red-100 rounded-lg" role="alert">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama Lengkap</label>
                    <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" value="{{ old('name', $user->name) }}" required>
                </div>
                <div>
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                    <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" value="{{ old('email', $user->email) }}" required>
                </div>
                <div>
                    <label for="phone_number" class="block mb-2 text-sm font-medium text-gray-900">Nomor Telepon</label>
                    <input type="tel" name="phone_number" id="phone_number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" value="{{ old('phone_number', $user->phone_number) }}">
                </div>
                 <div class="md:col-span-2">
                    <label for="address" class="block mb-2 text-sm font-medium text-gray-900">Alamat</label>
                    <textarea name="address" id="address" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">{{ old('address', $user->address) }}</textarea>
                </div>
                <hr class="md:col-span-2">
                <p class="md:col-span-2 text-sm text-gray-600">Kosongkan password jika tidak ingin mengubahnya.</p>
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Password Baru</label>
                    <input type="password" name="password" id="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                </div>
                <div>
                    <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-center text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300">Simpan Perubahan</button>
                <a href="{{ route('users.index') }}" class="ml-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg px-5 py-2.5 hover:bg-gray-100 hover:text-primary-700">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
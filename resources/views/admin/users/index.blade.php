@extends('layouts.app')

@section('title', 'Manajemen Pengguna (Orang Tua)')

@section('content')
<div class="p-4 sm:p-0">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Manajemen Pengguna (Orang Tua)</h1>
    
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama</th>
                    <th scope="col" class="px-6 py-3">Email & Telepon</th>
                    <th scope="col" class="px-6 py-3">Tanggal Bergabung</th>
                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ $user->name }}</th>
                    <td class="px-6 py-4">{{ $user->email }}<br><span class="text-xs text-gray-500">{{ $user->phone_number ?? '-' }}</span></td>
                    <td class="px-6 py-4">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('users.edit', $user->id) }}" class="font-medium text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-medium text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data pengguna untuk ditampilkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection
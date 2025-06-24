@extends('layouts.app')

@section('title', 'Manajemen Pengguna (Orang Tua)')

@section('content')
<div class="p-4 sm:p-0">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Manajemen Pengguna (Orang Tua)</h1>
    
    {{-- Kontainer tabel dengan sudut membulat dan shadow --}}
    <div class="overflow-x-auto rounded-lg shadow">
        <table class="w-full text-sm text-left text-gray-600">
            {{-- Header Tabel diubah warnanya --}}
            <thead class="text-xs text-white uppercase bg-[#f76eb0]">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama</th>
                    <th scope="col" class="px-6 py-3">Email & Telepon</th>
                    <th scope="col" class="px-6 py-3">Tanggal Bergabung</th>
                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            {{-- Body Tabel diubah warnanya --}}
            <tbody class="bg-gray-50">
                @forelse ($users as $user)
                {{-- Baris tabel dengan warna baru dan efek hover yang serasi --}}
                <tr class="border-b border-pink-200 hover:bg-pink-200">
                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ $user->name }}</th>
                    <td class="px-6 py-4">{{ $user->email }}<br><span class="text-xs text-gray-500">{{ $user->phone_number ?? '-' }}</span></td>
                    <td class="px-6 py-4">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="px-6 py-4 text-center">
                        {{-- Tombol aksi disesuaikan warnanya --}}
                        <a href="{{ route('users.edit', $user->id) }}" class="font-medium text-pink-600 hover:underline">Edit</a>
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-medium text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                {{-- Pesan jika data kosong, disesuaikan dengan latar belakang baru --}}
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data pengguna untuk ditampilkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{-- Pagination (tampilan default Laravel) --}}
        {{ $users->links() }}
    </div>
</div>
@endsection
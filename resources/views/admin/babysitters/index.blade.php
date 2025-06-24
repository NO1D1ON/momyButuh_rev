@extends('layouts.app')

@section('title', 'Manajemen Babysitter')

@section('content')
<div class="p-4 sm:p-0">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Babysitter</h1>
        <a href="{{ route('babysitters.create') }}" class="inline-block px-4 py-2 text-sm font-medium text-white rounded-lg bg-[#F564A9] hover:bg-[#E9559F]">
            + Tambah Babysitter
        </a>
    </div>

    <div class="overflow-x-auto rounded-lg shadow">
        <table class="w-full text-sm text-left text-gray-700">
            <thead class="text-xs uppercase bg-[#f76eb0]">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama</th>
                    <th scope="col" class="px-6 py-3">Email</th>
                    <th scope="col" class="px-6 py-3">Telepon</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-gray-50">
                @forelse ($babysitters as $babysitter)
                <tr class="border-b hover:bg-[#fcddec]">
                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                        {{ $babysitter->name }}
                    </th>
                    <td class="px-6 py-4">{{ $babysitter->email }}</td>
                    <td class="px-6 py-4">{{ $babysitter->phone_number ?? '-' }}</td>
                    <td class="px-6 py-4">
                        @if ($babysitter->is_available)
                            <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">Tersedia</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">Tidak Tersedia</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 flex items-center space-x-3">
                        <a href="{{ route('babysitters.edit', $babysitter->id) }}" class="font-medium text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('babysitters.destroy', $babysitter->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-medium text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        Tidak ada data.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $babysitters->links() }}
    </div>
</div>
@endsection

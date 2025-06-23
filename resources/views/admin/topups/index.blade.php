@extends('layouts.app')

@section('title', 'Manajemen Top Up')

@section('content')
<div class="p-4 sm:p-0">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Manajemen Top Up Saldo</h1>
    
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">Pengguna</th>
                    <th scope="col" class="px-6 py-3">Jumlah</th>
                    <th scope="col" class="px-6 py-3">Bukti Bayar</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($topups as $topup)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $topup->user->name }}</td>
                    <td class="px-6 py-4">Rp {{ number_format($topup->amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        @if($topup->payment_proof)
                            <a href="{{ asset('storage/' . $topup->payment_proof) }}" target="_blank" class="text-blue-600 hover:underline">Lihat Bukti</a>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 font-semibold text-xs rounded-full 
                            @if($topup->status == 'success') text-green-800 bg-green-100 @endif
                            @if($topup->status == 'pending') text-yellow-800 bg-yellow-100 @endif
                            @if($topup->status == 'failed') text-red-800 bg-red-100 @endif">
                            {{ ucfirst($topup->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if ($topup->status == 'pending')
                            <form action="{{ route('topups.approve', $topup->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Anda yakin ingin menyetujui top up ini?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="font-medium text-green-600 hover:underline">Setujui</button>
                            </form>
                            {{-- Form untuk menolak akan kita sederhanakan untuk saat ini --}}
                            <form action="{{ route('topups.reject', $topup->id) }}" method="POST" class="inline-block ml-4" onsubmit="let reason = prompt('Masukkan alasan penolakan:'); if (reason) { this.admin_notes.value = reason; return true; } else { return false; }">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="admin_notes" value="">
                                <button type="submit" class="font-medium text-red-600 hover:underline">Tolak</button>
                            </form>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada permintaan top up.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $topups->links() }}
    </div>
</div>
@endsection
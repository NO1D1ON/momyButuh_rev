@extends('layouts.app')

@section('title', 'Manajemen Booking')

@section('content')
<div class="p-4 sm:p-0">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Riwayat Transaksi Booking</h1>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">ID</th>
                    <th scope="col" class="px-6 py-3">Orang Tua</th>
                    <th scope="col" class="px-6 py-3">Babysitter</th>
                    <th scope="col" class="px-6 py-3">Tanggal</th>
                    <th scope="col" class="px-6 py-3">Total Harga</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4 font-mono text-xs">#{{ $booking->id }}</td>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $booking->user->name }}</td>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $booking->babysitter->name }}</td>
                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</td>
                    <td class="px-6 py-4">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        @if ($booking->status == 'completed')
                            <span class="px-2 py-1 font-semibold text-green-800 bg-green-100 rounded-full text-xs">Completed</span>
                        @elseif ($booking->status == 'confirmed')
                            <span class="px-2 py-1 font-semibold text-blue-800 bg-blue-100 rounded-full text-xs">Confirmed</span>
                        @elseif ($booking->status == 'cancelled')
                            <span class="px-2 py-1 font-semibold text-red-800 bg-red-100 rounded-full text-xs">Cancelled</span>
                        @else
                            <span class="px-2 py-1 font-semibold text-yellow-800 bg-yellow-100 rounded-full text-xs">Pending</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada transaksi booking.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $bookings->links() }}
    </div>
</div>
@endsection
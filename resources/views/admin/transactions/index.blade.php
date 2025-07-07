@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="p-4 sm:p-0">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Riwayat Transaksi</h1>

    <div class="mb-4 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="transaction-tabs" data-tabs-toggle="#transaction-tabs-content" role="tablist">
            <li class="me-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg" id="bookings-tab" data-tabs-target="#bookings" type="button" role="tab" aria-controls="bookings" aria-selected="true">Pemesanan</button>
            </li>
            <li class="me-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300" id="topups-tab" data-tabs-target="#topups" type="button" role="tab" aria-controls="topups" aria-selected="false">Top Up</button>
            </li>
        </ul>
    </div>

    <div id="transaction-tabs-content">
        {{-- Konten Tab Pemesanan --}}
        <div class="p-4 bg-white rounded-lg shadow" id="bookings" role="tabpanel" aria-labelledby="bookings-tab">
            @include('admin.bookings._table', ['bookings' => $bookings])
        </div>

        {{-- Konten Tab Top Up --}}
        <div class="p-4 bg-white rounded-lg shadow" id="topups" role="tabpanel" aria-labelledby="topups-tab">
            @include('admin.topups._table', ['topups' => $topups])
        </div>
    </div>
</div>
@endsection
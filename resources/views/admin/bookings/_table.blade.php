<div class="overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-500">
        {{-- ... (kode thead tabel booking seperti sebelumnya) ... --}}
        <tbody>
            @forelse ($bookings as $booking)
                {{-- ... (kode tr dan td tabel booking seperti sebelumnya) ... --}}
            @empty
                {{-- ... (kode tr empty seperti sebelumnya) ... --}}
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $bookings->links('pagination::tailwind') }}</div>
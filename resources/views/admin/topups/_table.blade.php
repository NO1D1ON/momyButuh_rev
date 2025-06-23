<div class="overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-500">
        {{-- ... (kode thead tabel topup seperti sebelumnya) ... --}}
        <tbody>
            @forelse ($topups as $topup)
                {{-- ... (kode tr dan td tabel topup seperti sebelumnya) ... --}}
            @empty
                {{-- ... (kode tr empty seperti sebelumnya) ... --}}
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $topups->links('pagination::tailwind') }}</div>
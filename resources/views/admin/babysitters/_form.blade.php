{{-- resources/views/admin/babysitters/_form.blade.php --}}

@if ($errors->any())
    <div class="p-4 mb-4 text-sm text-red-800 bg-red-100 rounded-lg" role="alert">
        <span class="font-medium">Terjadi kesalahan!</span>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama Lengkap</label>
        <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" value="{{ old('name', $babysitter->name ?? '') }}" required>
    </div>
    <div>
        <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Email</label>
        <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" value="{{ old('email', $babysitter->email ?? '') }}" required>
    </div>
    <div>
        <label for="phone_number" class="block mb-2 text-sm font-medium text-gray-900">Nomor Telepon</label>
        <input type="text" name="phone_number" id="phone_number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" value="{{ old('phone_number', $babysitter->phone_number ?? '') }}">
    </div>
    <div>
        <label for="birth_date" class="block mb-2 text-sm font-medium text-gray-900">Tanggal Lahir</label>
        <input type="date" name="birth_date" id="birth_date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" value="{{ old('birth_date', $babysitter->birth_date ?? '') }}" required>
    </div>
    <div class="md:col-span-2">
        <label for="address" class="block mb-2 text-sm font-medium text-gray-900">Alamat</label>
        <textarea name="address" id="address" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">{{ old('address', $babysitter->address ?? '') }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label for="bio" class="block mb-2 text-sm font-medium text-gray-900">Bio Singkat</label>
        <textarea name="bio" id="bio" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">{{ old('bio', $babysitter->bio ?? '') }}</textarea>
    </div>
    <div>
        <label for="rate_per_hour" class="block mb-2 text-sm font-medium text-gray-900">Tarif per Jam (Rp)</label>
        <input type="number" name="rate_per_hour" id="rate_per_hour" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" value="{{ old('rate_per_hour', $babysitter->rate_per_hour ?? '0') }}" required>
    </div>
    @if(isset($is_edit) && $is_edit)
    <div>
        <label for="is_available" class="block mb-2 text-sm font-medium text-gray-900">Status Ketersediaan</label>
        <select name="is_available" id="is_available" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
            <option value="1" {{ old('is_available', $babysitter->is_available) == 1 ? 'selected' : '' }}>Tersedia</option>
            <option value="0" {{ old('is_available', $babysitter->is_available) == 0 ? 'selected' : '' }}>Tidak Tersedia</option>
        </select>
    </div>
    @endif
</div>
<div class="mt-6">
    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-center text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300">
        Simpan Data
    </button>
    <a href="{{ route('babysitters.index') }}" class="ml-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg px-5 py-2.5 hover:bg-gray-100 hover:text-primary-700">
        Batal
    </a>
</div>
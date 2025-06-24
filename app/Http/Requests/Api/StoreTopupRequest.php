<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreTopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Izinkan semua user terotentikasi
    }

    public function rules(): array
    {
        return [
            // Minimal top up Rp 10.000
            'amount' => 'required|integer|min:10000',
            // Harus berupa gambar, format (jpg, png), maks 2MB
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
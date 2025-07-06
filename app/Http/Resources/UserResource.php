<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Secara default, Laravel akan mengembalikan semua atribut model.
        // Namun, untuk memastikan, kita akan secara eksplisit mendefinisikan
        // semua atribut yang ingin kita kirim, termasuk 'balance'.
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            
            // --- PERBAIKAN UTAMA: Tambahkan 'balance' ke dalam respons API ---
            // Ini memastikan saldo akan selalu disertakan saat data user dikirim.
            // Kita juga melakukan casting ke float di sini untuk keamanan ganda.
            'balance' => (float) $this->balance,
            
            // Anda juga bisa menambahkan atribut lain di sini jika perlu
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

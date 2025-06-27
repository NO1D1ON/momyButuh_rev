<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\UserRessource;

class BabysitterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // PASTIKAN SEMUA FIELD INI ADA DAN SESUAI DENGAN MODEL FLUTTER ANDA
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'bio' => $this->bio,
            'age' => $this->age, // Ini akan otomatis memanggil getAgeAttribute()
            'address' => $this->address,
            'rate_per_hour' => $this->rate_per_hour,
            'rating' => (float) $this->rating, // Pastikan tipe data double/float
            'experience_years' => $this->experience_years,
            
            // Sertakan reviews jika sudah di-load
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            
            // Anda juga bisa menambahkan data lain yang relevan di sini
            'is_available' => $this->is_available,
            'phone_number' => $this->phone_number,
            'distance' => $this->when(isset($this->distance), $this->distance) // Hanya sertakan jika ada
        ];
    }
}
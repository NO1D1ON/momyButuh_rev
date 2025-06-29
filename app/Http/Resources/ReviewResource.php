<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Resource ini akan memformat setiap objek Review
        return [
            'id' => $this->id,
            'rating' => (float) $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at->format('d F Y'),
            
            // Kita juga sertakan data user yang memberikan review
            // Ini akan menggunakan UserResource untuk konsistensi
            'user' => new UserResource($this->whenLoaded('user'))
        ];
    }
}

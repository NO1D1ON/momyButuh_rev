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
        // Resource ini hanya akan menampilkan data user yang aman untuk publik
        return [
            'id' => $this->id,
            'name' => $this->name,
            // Jangan sertakan email atau data sensitif lainnya di sini
        ];
    }
}

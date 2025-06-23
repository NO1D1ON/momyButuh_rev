<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BabysitterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'age' => \Carbon\Carbon::parse($this->birth_date)->age,
            'address' => $this->address,
            'bio' => $this->bio,
            'rate_per_hour' => $this->rate_per_hour,
            'is_available' => $this->is_available,
        ];
    }
}
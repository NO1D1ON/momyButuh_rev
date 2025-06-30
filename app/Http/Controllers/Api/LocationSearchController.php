<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocationSearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate(['query' => 'required|string|min:3']);
        $query = $request->input('query');
        $apiKey = env('Maps_API_KEY'); // Pastikan Anda menyimpan API Key di file .env

        $response = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
            'input' => $query,
            'key' => $apiKey,
            'components' => 'country:id', // Batasi pencarian hanya di Indonesia
        ]);

        return $response->json();
    }

    public function getDetails(Request $request)
    {
        $request->validate(['place_id' => 'required|string']);
        $placeId = $request->input('place_id');
        $apiKey = env('AIzaSyDX0NUe4AbmA10BGiWVpyD28AYeW0Z7TTk');

        $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $placeId,
            'key' => $apiKey,
            'fields' => 'name,formatted_address,geometry', // Ambil nama, alamat, dan koordinat
        ]);

        return $response->json();
    }
}
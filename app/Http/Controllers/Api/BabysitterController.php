<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Babysitter;
use App\Http\Resources\BabysitterResource;

class BabysitterController extends Controller
{
    // Menampilkan semua babysitter yang tersedia
    public function index()
    {
        $babysitters = Babysitter::where('is_available', true)->get();
        return BabysitterResource::collection($babysitters);
    }

    // Menampilkan detail satu babysitter
    public function show(Babysitter $babysitter)
    {
        return new BabysitterResource($babysitter);
    }
}
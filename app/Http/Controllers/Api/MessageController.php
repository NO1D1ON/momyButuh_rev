<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Babysitter;
use App\Models\Conversation;
use App\Models\Message; // Pastikan Message di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // Melihat daftar percakapan milik user yang login
    public function index(Request $request)
    {
        $user = $request->user();
        // Ambil percakapan dimana user adalah 'Orang Tua'
        $conversations = Conversation::where('user_id', $user->id)
                            ->with('babysitter:id,name') // Ambil data ringkas babysitter
                            ->get();

        return response()->json($conversations);
    }

    // Melihat detail pesan dalam satu percakapan
    public function show(Request $request, Conversation $conversation)
    {
        // Otorisasi: pastikan user yang login adalah bagian dari percakapan
        if ($request->user()->id !== $conversation->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()->latest()->get();
        return response()->json($messages);
    }

    // Mengirim pesan baru
    public function getOrCreateConversation(Request $request, $babysitterId)
    {
        // Cari babysitter secara manual untuk kontrol error yang lebih baik
        $babysitter = Babysitter::find($babysitterId);

        if (!$babysitter) {
            return response()->json(['message' => 'Babysitter tidak ditemukan.'], 404);
        }

        $user = $request->user();

        $conversation = Conversation::firstOrCreate(
            [
                'user_id' => $user->id,
                'babysitter_id' => $babysitter->id,
            ]
        );

        $conversation->load('messages');

        return response()->json($conversation);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'babysitter_id' => 'required|exists:babysitters,id',
            'body' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        $conversation = Conversation::firstOrCreate(
            [
                'user_id' => $user->id,
                'babysitter_id' => $validated['babysitter_id'],
            ]
        );

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'sender_type' => get_class($user),
            'body' => $validated['body'],
        ]);

        return response()->json($message, 201);
    }
}
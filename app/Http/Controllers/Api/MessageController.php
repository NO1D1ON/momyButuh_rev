<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

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
    public function store(Request $request)
    {
        $request->validate([
            'babysitter_id' => 'required|exists:babysitters,id',
            'body' => 'required|string',
        ]);

        $user = $request->user();

        // Cari atau buat percakapan baru antara user dan babysitter
        $conversation = Conversation::firstOrCreate([
            'user_id' => $user->id,
            'babysitter_id' => $request->babysitter_id,
        ]);

        // Buat pesan baru
        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'sender_type' => get_class($user), // 'App\Models\User'
            'body' => $request->body,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message, 201);
    }

    public function getOrCreateConversation(Request $request, Babysitter $babysitter)
    {
        $user = $request->user();

        $conversation = Conversation::firstOrCreate(
            [
                'user_id' => $user->id,
                'babysitter_id' => $babysitter->id,
            ]
        );

        // Muat pesan-pesan yang ada di dalam percakapan ini
        $conversation->load('messages');

        return response()->json($conversation);
    }
}
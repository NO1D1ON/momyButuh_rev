<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Babysitter;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent; // Event untuk broadcasting
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    /**
     * Menampilkan semua percakapan milik user/babysitter yang login.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userType = get_class($user);
        $userIdField = $userType === 'App\Models\User' ? 'user_id' : 'babysitter_id';

        $conversations = Conversation::where($userIdField, $user->id)
            ->with([
                $userIdField === 'user_id' ? 'babysitter:id,name' : 'user:id,name',
                'messages' => fn($query) => $query->latest()->limit(1)
            ])
            ->latest('updated_at')
            ->get();

        return response()->json($conversations);
    }

    /**
     * Mendapatkan atau membuat percakapan dengan babysitter.
     */
    public function getOrCreateConversation(Request $request, $babysitterId)
    {
        // Cari babysitter secara manual untuk kontrol error yang lebih baik
        $babysitter = Babysitter::find($babysitterId);

        // Jika tidak ditemukan, kembalikan 404 yang jelas
        if (!$babysitter) {
            return response()->json(['message' => 'Babysitter tidak ditemukan.'], 404);
        }

        $user = $request->user();

        // Cari atau buat percakapan baru
        $conversation = Conversation::firstOrCreate(
            [
                'user_id' => $user->id,
                'babysitter_id' => $babysitter->id,
            ]
        );

        // Muat pesan-pesan yang ada di dalamnya untuk ditampilkan di layar chat
        // Diurutkan dari yang terbaru, dan dibatasi 50 pesan terakhir (untuk performa)
        $conversation->load(['messages' => function ($query) {
            $query->latest()->limit(50);
        }]);

        return response()->json($conversation);
    }

    /**
     * Menyimpan pesan baru dan menyiarkannya secara real-time.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'babysitter_id' => 'required|exists:babysitters,id',
            'body' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        $conversation = Conversation::firstOrCreate([
            'user_id' => $user->id,
            'babysitter_id' => $validated['babysitter_id']
        ]);

        if ($user->id !== $conversation->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'sender_type' => get_class($user),
            'body' => $validated['body'],
        ]);

        $message->load('sender:id,name');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message, 201);
    }

    /**
     * Menandai pesan sebagai sudah dibaca
     */
    public function markAsRead(Request $request, Conversation $conversation)
    {
        try {
            $user = $request->user();
            
            // Otorisasi
            if ($user->id !== $conversation->user_id && $user->id !== $conversation->babysitter_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Mark semua pesan dari lawan bicara sebagai sudah dibaca
            $updatedCount = $conversation->messages()
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json([
                'status' => 'success',
                'message' => "Marked {$updatedCount} messages as read"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark messages as read'
            ], 500);
        }
    }

    /**
     * Menghitung jumlah pesan yang belum dibaca
     */
    public function getUnreadCount(Request $request)
    {
        try {
            $user = $request->user();
            
            $unreadCount = Message::whereIn('conversation_id', function($query) use ($user) {
                $query->select('id')
                      ->from('conversations')
                      ->where('user_id', $user->id)
                      ->orWhere('babysitter_id', $user->id);
            })
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();

            return response()->json([
                'status' => 'success',
                'data' => ['unread_count' => $unreadCount]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get unread count'
            ], 500);
        }
    }

    /**
     * Mendapatkan status online user (untuk real-time presence)
     */
    public function getUserStatus(Request $request, $userId)
    {
        try {
            $isOnline = Cache::has("user_online_{$userId}");
            $lastSeen = Cache::get("user_last_seen_{$userId}");

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user_id' => $userId,
                    'is_online' => $isOnline,
                    'last_seen' => $lastSeen
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get user status'
            ], 500);
        }
    }

    /**
     * Update status online user
     */
    public function updateOnlineStatus(Request $request)
    {
        try {
            $user = $request->user();
            $cacheKey = "user_online_{$user->id}";
            $lastSeenKey = "user_last_seen_{$user->id}";
            
            // Set user as online for 5 minutes
            Cache::put($cacheKey, true, 300);
            Cache::put($lastSeenKey, now(), 86400); // Keep last seen for 24 hours

            return response()->json([
                'status' => 'success',
                'message' => 'Online status updated'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update online status'
            ], 500);
        }
    }
}
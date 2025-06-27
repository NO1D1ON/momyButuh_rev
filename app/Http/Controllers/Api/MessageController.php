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
use Illuminate\Http\JsonResponse;
use App\Events\UserTyping;
use App\Events\MessageRead;


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
     * Mengambil semua percakapan untuk pengguna yang sedang login.
     * Percakapan akan menyertakan pesan terakhir dan data lawan bicara.
     */
    public function conversations(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::where('user_id', $user->id)
            ->orWhere('babysitter_id', $user->id)
            ->with([
                'user:id,name',
                'babysitter:id,name',
                'latestMessage'
            ])
            ->get();

        $formattedConversations = $conversations->map(function ($convo) use ($user) {
            $otherParty = $user->id === $convo->user_id ? $convo->babysitter : $convo->user;
            
            return [
                'conversation_id' => $convo->id,
                'other_party_id' => $otherParty->id,
                'other_party_name' => $otherParty->name,
                'last_message' => $convo->latestMessage->body ?? 'Belum ada pesan',
                // PERBAIKAN: Gunakan optional chaining (?->) untuk mencegah error jika latestMessage null
                'last_message_time' => $convo->latestMessage?->created_at->diffForHumans() ?? '',
            ];
        });

        return response()->json($formattedConversations);
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
     * Menandai semua pesan yang belum dibaca dalam sebuah percakapan sebagai telah dibaca.
     *
     * @param Request $request
     * @param Conversation $conversation
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, Conversation $conversation)
    {
        // Otorisasi
        if ($request->user()->id !== $conversation->user_id && $request->user()->id !== $conversation->babysitter_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = $request->user();

        // PERBAIKAN: Kueri ini lebih jelas dan aman.
        // Ia mencari pesan yang belum dibaca dan TIDAK dikirim oleh user saat ini.
        $conversation->messages()
            ->whereNull('read_at')
            ->whereNot(function ($query) use ($user) {
                $query->where('sender_type', get_class($user))
                      ->where('sender_id', $user->id);
            })
            ->update(['read_at' => now()]);

        // Siarkan event bahwa pesan telah dibaca ke client lain
        broadcast(new MessageRead($conversation))->toOthers();

        return response()->json(['message' => 'Messages marked as read']);
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

    /**
     * Mengambil riwayat pesan untuk sebuah percakapan.
     * Menggunakan paginasi untuk efisiensi.
     *
     * @param Request $request
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function getMessages(Request $request, Conversation $conversation): JsonResponse
    {
        // ... (Isi metode tetap sama)
        if ($request->user()->id !== $conversation->user_id && $request->user()->id !== $conversation->babysitter_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->latest()
            ->paginate(50);

        return response()->json($messages);
    }

    public function startTyping(Request $request, Conversation $conversation)
    {
        // Otorisasi sederhana
        if ($request->user()->id !== $conversation->user_id && $request->user()->id !== $conversation->babysitter_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Siarkan event ke client lain di channel yang sama
        broadcast(new UserTyping($request->user(), $conversation))->toOthers();

        return response()->json(['message' => 'Typing event broadcasted']);
    }

    public function initiateConversation(Request $request)
    {
        // Validasi input untuk memastikan babysitter_id ada dan valid
        $validated = $request->validate([
            'babysitter_id' => 'required|exists:babysitters,id',
        ]);

        $user = $request->user();

        // Cari percakapan yang sudah ada, atau buat yang baru jika tidak ditemukan.
        // Ini mencegah duplikasi record percakapan antara dua pengguna yang sama.
        $conversation = \App\Models\Conversation::firstOrCreate(
            [
                'user_id' => $user->id,
                'babysitter_id' => $validated['babysitter_id'],
            ]
        );

        // Muat relasi agar data user dan babysitter ikut terkirim
        $conversation->load(['user:id,name', 'babysitter:id,name']);

        // Kembalikan data percakapan yang lengkap (termasuk ID-nya)
        return response()->json($conversation);
    }
}
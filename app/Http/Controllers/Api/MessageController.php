<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Babysitter;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use App\Events\UserTyping;
use App\Events\MessageRead;

// --- TAMBAHKAN IMPORT INI ---
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MessageController extends Controller
{
    // --- TAMBAHKAN TRAIT INI ---
    use AuthorizesRequests;

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
            
            $lastMessageText = $convo->latestMessage ? $convo->latestMessage->body : 'Belum ada pesan';
            $lastMessageTime = $convo->latestMessage ? $convo->latestMessage->created_at->diffForHumans() : '';

            return [
                'conversation_id' => $convo->id,
                'other_party_id' => $otherParty->id,
                'other_party_name' => $otherParty->name,
                'last_message' => $lastMessageText,
                'last_message_time' => $lastMessageTime,
            ];
        });

        return response()->json($formattedConversations);
    }

    /**
     * Mendapatkan atau membuat percakapan dengan babysitter.
     */
    public function getOrCreateConversation(Request $request, $babysitterId)
    {
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
    $sender = $request->user();

    // Tentukan tabel receiver berdasarkan siapa pengirimnya
    if ($sender instanceof \App\Models\User) {
        $receiverTable = 'babysitters';
    } elseif ($sender instanceof \App\Models\Babysitter) {
        $receiverTable = 'users';
    } else {
        return response()->json(['message' => 'Tipe pengirim tidak valid'], 400);
    }

    // Validasi input, termasuk memeriksa apakah receiver_id ada di tabel yang benar
    $validated = $request->validate([
        'body' => 'required|string|max:1000',
        'receiver_id' => 'required|integer|exists:' . $receiverTable . ',id',
    ], [
        // Pesan error kustom agar lebih jelas
        'receiver_id.exists' => 'Pengguna penerima tidak ditemukan.',
    ]);

    // Logika untuk menentukan user_id dan babysitter_id
    if ($sender instanceof \App\Models\User) {
        $userId = $sender->id;
        $babysitterId = $validated['receiver_id'];
    } else { // Jika pengirim adalah Babysitter
        $userId = $validated['receiver_id'];
        $babysitterId = $sender->id;
    }

    // Temukan atau buat percakapan baru
    $conversation = Conversation::firstOrCreate([
        'user_id' => $userId,
        'babysitter_id' => $babysitterId,
    ]);

    // Buat pesan baru dalam percakapan
    $message = $conversation->messages()->create([
        'sender_id' => $sender->id,
        'sender_type' => get_class($sender),
        'body' => $validated['body'],
    ]);

    // Siarkan event bahwa ada pesan baru
    broadcast(new \App\Events\MessageSent($message))->toOthers();

    // Kembalikan response sukses beserta data pesan yang baru dibuat
    return response()->json($message->load('sender:id,name'), 201);
}
    
    /**
     * Mengambil riwayat pesan untuk sebuah percakapan.
     * --- METODE YANG DIPERBAIKI ---
     * @param Request $request
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function getMessages(Request $request, Conversation $conversation): JsonResponse
    {
        // Otorisasi sekarang ditangani oleh ConversationPolicy.
        $this->authorize('view', $conversation);

        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->latest()
            ->paginate(50);

        return response()->json($messages);
    }

    /**
     * Menandai semua pesan yang belum dibaca dalam sebuah percakapan sebagai telah dibaca.
     */
    public function markAsRead(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation); // Bisa gunakan policy yang sama

        $user = $request->user();

        $conversation->messages()
            ->whereNull('read_at')
            ->whereNot(function ($query) use ($user) {
                $query->where('sender_type', get_class($user))
                      ->where('sender_id', $user->id);
            })
            ->update(['read_at' => now()]);

        broadcast(new MessageRead($conversation))->toOthers();

        return response()->json(['message' => 'Messages marked as read']);
    }

    // ... (sisa metode lainnya seperti getUnreadCount, getUserStatus, dll.)
    
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

    public function updateOnlineStatus(Request $request)
    {
        try {
            $user = $request->user();
            $cacheKey = "user_online_{$user->id}";
            $lastSeenKey = "user_last_seen_{$user->id}";
            
            Cache::put($cacheKey, true, 300);
            Cache::put($lastSeenKey, now(), 86400);

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

    public function startTyping(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        broadcast(new UserTyping($request->user(), $conversation))->toOthers();

        return response()->json(['message' => 'Typing event broadcasted']);
    }

    public function initiateConversation(Request $request)
    {
        $validated = $request->validate([
            'babysitter_id' => 'required|exists:babysitters,id',
        ]);

        $user = $request->user();

        $conversation = Conversation::firstOrCreate(
            [
                'user_id' => $user->id,
                'babysitter_id' => $validated['babysitter_id'],
            ]
        );

        $conversation->load(['user:id,name', 'babysitter:id,name']);

        return response()->json($conversation);
    }
}
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Babysitter;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use App\Events\MessageRead;
use App\Events\UserTyping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $babysitter;
    protected $conversation;

    /**
     * Menyiapkan lingkungan pengujian.
     * Method ini dijalankan sebelum setiap method test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Buat user (Orang Tua) dan Babysitter
        $this->user = User::factory()->create();
        $this->babysitter = Babysitter::factory()->create();

        // Buat percakapan antara mereka
        $this->conversation = Conversation::create([
            'user_id' => $this->user->id,
            'babysitter_id' => $this->babysitter->id,
        ]);
    }

    /** @test */
    public function user_can_fetch_their_conversations()
    {
        Sanctum::actingAs($this->user); // Login sebagai user

        $response = $this->getJson('/api/conversations');

        $response->assertStatus(200)
                 ->assertJsonFragment(['conversation_id' => $this->conversation->id]);
    }

    /** @test */
    public function user_can_fetch_messages_from_a_conversation()
    {
        Sanctum::actingAs($this->user);

        Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->babysitter->id,
            'sender_type' => Babysitter::class,
        ]);

        $response = $this->getJson("/api/conversations/{$this->conversation->id}/messages");

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data'); // Memastikan ada 1 pesan
    }

    /** @test */
    public function user_can_send_a_message()
    {
        Event::fake(); // Mencegah event berjalan sungguhan
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/messages', [
            'body' => 'Halo Babysitter!',
            'babysitter_id' => $this->babysitter->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('messages', ['body' => 'Halo Babysitter!']);
        Event::assertDispatched(MessageSent::class); // Memastikan event MessageSent terkirim
    }

    /** @test */
    public function user_can_mark_messages_as_read()
    {
        // PERBAIKAN: Tambahkan baris ini untuk mendapatkan detail error yang lengkap
        $this->withoutExceptionHandling();

        Event::fake();
        Sanctum::actingAs($this->babysitter); // Login sebagai penerima

        // Buat pesan dari pengirim
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->user->id,
            'sender_type' => User::class,
            'read_at' => null,
        ]);

        // Langkah 1: Pastikan kondisi awal sudah benar
        $this->assertNull($message->read_at);

        // Langkah 2: Panggil API
        $response = $this->postJson("/api/conversations/{$this->conversation->id}/read");

        // Langkah 3: Periksa respons dan hasilnya
        $response->assertStatus(200);

        // Gunakan assertion yang lebih andal.
        // Cukup periksa bahwa 'read_at' tidak lagi null setelah di-refresh dari DB.
        $this->assertNotNull($message->fresh()->read_at);

        Event::assertDispatched(MessageRead::class);
    }

    /** @test */
    public function user_can_broadcast_typing_event()
    {
        Event::fake();
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/conversations/{$this->conversation->id}/typing");
        
        $response->assertStatus(200);
        Event::assertDispatched(UserTyping::class);
    }
    
    /** @test */
    public function unauthorized_user_cannot_access_conversation()
    {
        // Buat user baru yang tidak ada hubungannya dengan percakapan
        $unauthorizedUser = User::factory()->create();
        Sanctum::actingAs($unauthorizedUser);

        $response = $this->getJson("/api/conversations/{$this->conversation->id}/messages");

        $response->assertStatus(403); // Memastikan akses ditolak (Forbidden)
    }
}

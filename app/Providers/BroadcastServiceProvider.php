<?php
namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // PERBAIKAN FINAL: Hapus middleware 'api' dan hanya gunakan 'auth:sanctum,babysitter'.
        // Ini memastikan otentikasi token berjalan bersih tanpa konflik dari middleware lain.
        Broadcast::routes(['middleware' => ['auth:sanctum,babysitter']]);

        require base_path('routes/channels.php');
    }
}

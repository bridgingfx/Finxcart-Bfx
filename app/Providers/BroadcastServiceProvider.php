<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Use 'web' middleware group which includes session handling
       Broadcast::routes(['middleware' => ['web', 'auth:web,admin,customer']]);

        require base_path('routes/channels.php');
    }
}

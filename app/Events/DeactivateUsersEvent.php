<?php
namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

class DeactivateUsersEvent
{
    use SerializesModels;

    public function handle()
    {
        User::where('deactivation_time', '<=', now())->update(['is_active' => 0]);
    }
}


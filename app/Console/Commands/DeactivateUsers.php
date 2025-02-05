<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\DhruvBtcUser;
use App\Models\SanghaniBtcUser;
use App\Models\WatchUser;

class DeactivateUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:deactivate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate users whose deactivation time has passed';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        User::where('deactivation_time', '<=', now())->update(['is_active' => 0]);
        DhruvBtcUser::where('deactivation_time', '<=', now())->update(['is_active' => 0]);
        SanghaniBtcUser::where('deactivation_time', '<=', now())->update(['is_active' => 0]);
        WatchUser::where('deactivation_time', '<=', now())->update(['is_active' => 0]);
        $this->info('Users deactivated successfully.');
    }
}

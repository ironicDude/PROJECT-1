<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOldSoftDeletedRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-old-soft-deleted-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = Carbon::now()->subDays(14);

        User::onlyTrashed()
            ->where('deleted_at', '<', $date)
            ->get()
            ->each
            ->forceDelete();
    }
}

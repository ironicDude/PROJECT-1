<?php

namespace App\Listeners;

use App\Events\UserAccountStatusChanged;
use App\Notifications\UserAccountStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOnAccountStatusChangeNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserAccountStatusChanged $event): void
    {
        $event->admin->notify(new UserAccountStatusChangedNotification($event->user, $event->status));
    }
}

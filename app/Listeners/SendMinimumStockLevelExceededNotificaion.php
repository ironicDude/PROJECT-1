<?php

namespace App\Listeners;

use App\Events\MinimumStockLevelExceeded;
use App\Notifications\MinimumStockLevelExceededNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use App\Models\Employee;

class SendMinimumStockLevelExceededNotificaion
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
    public function handle(MinimumStockLevelExceeded $event): void
    {
        if($event->inventoryManager){
            $event->inventoryManager->notify(new MinimumStockLevelExceededNotification($event->purchasedProduct));
        }
        $event->admin->notify(new MinimumStockLevelExceededNotification($event->purchasedProduct));
    }
}

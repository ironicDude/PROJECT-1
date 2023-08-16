<?php

namespace App\Listeners;

use App\Events\BrokePharmacy;
use App\Mail\PharmacyWentBroke;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendPharmacyWentBrokeMail
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
    public function handle(BrokePharmacy $event): void
    {
        $admin = $event->admin;
        Mail::to($admin)->send(new PharmacyWentBroke());
    }
}

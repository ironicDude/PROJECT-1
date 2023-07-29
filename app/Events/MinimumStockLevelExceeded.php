<?php

namespace App\Events;

use App\Models\Employee;
use App\Models\PurchasedProduct;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use PhpParser\Node\Expr\Empty_;

class MinimumStockLevelExceeded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $purchasedProduct;
    public $inventoryManager;
    public $admin;
    /**
     * Create a new event instance.
     */
    public function __construct(PurchasedProduct $purchasedProduct, Employee $inventoryManager, Employee $admin)
    {
        $this->purchasedProduct = $purchasedProduct;
        $this->inventoryManager = $inventoryManager;
        $this->admin = $admin;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('events'),
        ];
    }
}

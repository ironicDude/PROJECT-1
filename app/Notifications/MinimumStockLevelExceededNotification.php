<?php

namespace App\Notifications;

use App\Models\PurchasedProduct;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MinimumStockLevelExceededNotification extends Notification implements ShouldBroadcast
{
    use Queueable;


    protected $purchasedProduct;

     /**
     * Create a new notification instance.
     */
    public function __construct(PurchasedProduct $purchasedProduct)
    {
        $this->purchasedProduct = $purchasedProduct;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Product {$this->purchasedProduct->product->name} with id {$this->purchasedProduct->id} fell short of it's minimum stock level -{$this->purchasedProduct->minimum_stock_level}. Now, the system only allows for one item per order for this product. Try to provide it for the pharmacy",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => "Product {$this->purchasedProduct->product->name} with id {$this->purchasedProduct->id} fell short of it's minimum stock level -{$this->purchasedProduct->minimum_stock_level}. Now, the system only allows for one item per order for this product. Try to provide it for the pharmacy",
        ]);
    }
}

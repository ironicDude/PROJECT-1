<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderShipped extends Mailable
{
    use Queueable, SerializesModels;
/**
     * Create a new message instance.
     */

     public function __construct(private Order $order)
     {
     }

     /**
      * Get the message envelope.
      */
     public function envelope(): Envelope
     {
         return new Envelope(
             subject: 'Order Shipped',
         );
     }

     /**
      * Get the message content definition.
      */
     public function content(): Content
     {
         return new Content(
             view: 'emails.OrderShipped',
             with: [
                 'orderId' => $this->order->id,
                 'deliveryDate' => $this->order->delivery_date,
                 'deliveryFees' => $this->order->delivery_fees,
             ]
         );
     }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

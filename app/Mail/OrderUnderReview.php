<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Pharmacy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class OrderUnderReview extends Mailable
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
            subject: 'Order Under Review',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.OrderUnderReview',
            with: [
                'orderId' => $this->order->id,
                'total' => $this->order->getTotal(),
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
        $names = $this->order->prescriptions->pluck('prescription')->toArray();

        $attachments = [];

        foreach ($names as $name) {
            $attachment = Attachment::fromStorage($name)
                ->as($name)
                ->withMime('application/pdf');

            $attachments[] = $attachment;
        }
        return $attachments;
    }
}

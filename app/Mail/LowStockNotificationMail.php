<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly Product $product)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Low stock alert: '.$this->product->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.low_stock_notification',
            with: [
                'product' => $this->product,
            ],
        );
    }
}

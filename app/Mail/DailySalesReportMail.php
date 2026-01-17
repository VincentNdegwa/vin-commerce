<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySalesReportMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly string $reportName, private readonly array $data)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->reportName,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.daily_sales_report',
            with: [
                'reportName' => $this->reportName,
                'data' => $this->data,
            ],
        );
    }
}

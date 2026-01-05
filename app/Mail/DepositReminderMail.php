<?php

namespace App\Mail;

use App\Models\CoinTransaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DepositReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public CoinTransaction $transaction,
        public ?string $paymentUrl = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⏰ Rappel - Finalisez votre dépôt de pièces',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.deposit-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

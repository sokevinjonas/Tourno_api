<?php

namespace App\Mail;

use App\Models\CoinTransaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DepositCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public CoinTransaction $transaction
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Dépôt de pièces réussi',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.deposit-completed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use App\Models\CoinTransaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WithdrawalRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public CoinTransaction $transaction
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⏳ Demande de retrait en attente',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.withdrawal-requested',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

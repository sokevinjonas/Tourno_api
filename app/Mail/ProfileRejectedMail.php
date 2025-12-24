<?php

namespace App\Mail;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProfileRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Profile $profile,
        public string $rejectionReason
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre profil a été rejeté',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.profiles.rejected',
        );
    }
}

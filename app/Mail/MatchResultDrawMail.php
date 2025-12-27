<?php

namespace App\Mail;

use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MatchResultDrawMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $player,
        public TournamentMatch $match,
        public int $score
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Match nul - Résultat de votre match',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.matches.draw',
        );
    }
}

<?php

namespace App\Mail;

use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MatchResultLoserMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $loser,
        public TournamentMatch $match,
        public int $loserScore,
        public int $winnerScore
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Résultat de votre match - Continuez comme ça !',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.matches.loser',
        );
    }
}

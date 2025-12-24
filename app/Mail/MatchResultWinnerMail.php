<?php

namespace App\Mail;

use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MatchResultWinnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $winner,
        public TournamentMatch $match,
        public int $winnerScore,
        public int $loserScore
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Victoire ! Résultat de votre match',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.matches.winner',
        );
    }
}

<?php

namespace App\Mail;

use App\Models\Round;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NextRoundGeneratedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $player,
        public Tournament $tournament,
        public Round $round,
        public ?TournamentMatch $match = null
    ) {
        // Load profile relations for opponents
        if ($this->match) {
            if ($this->match->player1) {
                $this->match->player1->load('profile');
            }
            if ($this->match->player2) {
                $this->match->player2->load('profile');
            }
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nouveau Round disponible - {$this->tournament->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tournaments.next-round',
        );
    }
}

<?php

namespace App\Mail;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TournamentStartedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Tournament $tournament,
        public User $user,
        public ?TournamentMatch $firstMatch = null
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Le tournoi {$this->tournament->name} a commencé!",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $opponent = null;
        if ($this->firstMatch) {
            $opponentId = $this->firstMatch->player1_id === $this->user->id
                ? $this->firstMatch->player2_id
                : $this->firstMatch->player1_id;
            $opponent = User::find($opponentId);
        }

        return new Content(
            view: 'emails.tournaments.tournament-started',
            with: [
                'tournament' => $this->tournament,
                'user' => $this->user,
                'firstMatch' => $this->firstMatch,
                'opponent' => $opponent,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

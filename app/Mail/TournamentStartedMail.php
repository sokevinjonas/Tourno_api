<?php

namespace App\Mail;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Bus\Queueable;
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
        public $firstMatch = null,
        public $opponent = null
    ) {
        // Find opponent if first match exists
        if ($this->firstMatch) {
            if ($this->firstMatch->player1_id === $this->user->id) {
                $this->opponent = $this->firstMatch->player2;
            } elseif ($this->firstMatch->player2_id === $this->user->id) {
                $this->opponent = $this->firstMatch->player1;
            }

            // Charger la relation profile pour accéder au whatsapp_number
            if ($this->opponent) {
                $this->opponent->load('profile');
            }
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Le tournoi a commencé !',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tournaments.tournament-started',
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

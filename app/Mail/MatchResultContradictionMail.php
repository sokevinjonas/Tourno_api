<?php

namespace App\Mail;

use App\Models\TournamentMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MatchResultContradictionMail extends Mailable
{
    use Queueable, SerializesModels;

    public TournamentMatch $match;
    public $submission1;
    public $submission2;

    /**
     * Create a new message instance.
     */
    public function __construct(TournamentMatch $match, $submission1, $submission2)
    {
        $this->match = $match;
        $this->submission1 = $submission1;
        $this->submission2 = $submission2;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⚠️ Incohérence de score - {$this->match->tournament->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.matches.contradiction',
            with: [
                'match' => $this->match,
                'tournament' => $this->match->tournament,
                'player1' => $this->match->player1,
                'player2' => $this->match->player2,
                'submission1' => $this->submission1,
                'submission2' => $this->submission2,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}

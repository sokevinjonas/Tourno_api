<?php

namespace App\Mail;

use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MatchScoreCorrectedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $player,
        public TournamentMatch $match,
        public int $oldPlayerScore,
        public int $oldOpponentScore,
        public int $newPlayerScore,
        public int $newOpponentScore,
        public string $result // 'win', 'loss', or 'draw'
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match($this->result) {
            'win' => '✅ Correction de score - Vous avez gagné',
            'loss' => '❌ Correction de score - Vous avez perdu',
            'draw' => '🤝 Correction de score - Match nul',
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.match-score-corrected',
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

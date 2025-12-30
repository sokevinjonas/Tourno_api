<?php

namespace App\Mail;

use App\Models\MatchMessage;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MatchMessageNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public MatchMessage $matchMessage,
        public TournamentMatch $match,
        public User $sender,
        public User $recipient
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nouveau message de {$this->sender->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.matches.message-notification',
            with: [
                'matchMessage' => $this->matchMessage,
                'match' => $this->match,
                'sender' => $this->sender,
                'recipient' => $this->recipient,
                'tournament' => $this->match->tournament,
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

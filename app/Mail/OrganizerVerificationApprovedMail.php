<?php

namespace App\Mail;

use App\Models\OrganizerProfile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganizerVerificationApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $organizer,
        public OrganizerProfile $organizerProfile,
        public string $badge
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Félicitations ! Votre vérification a été approuvée',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.organizers.verification-approved',
            with: [
                'organizer' => $this->organizer,
                'organizerProfile' => $this->organizerProfile,
                'badge' => $this->badge,
                'badgeLabel' => $this->getBadgeLabel(),
                'badgeEmoji' => $this->getBadgeEmoji(),
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

    /**
     * Get badge label
     */
    private function getBadgeLabel(): string
    {
        return match ($this->badge) {
            'verified' => 'Organisateur Vérifié',
            'partner' => 'Partenaire Officiel',
            default => 'Organisateur',
        };
    }

    /**
     * Get badge emoji
     */
    private function getBadgeEmoji(): string
    {
        return match ($this->badge) {
            'verified' => '✓',
            'partner' => '★',
            default => '',
        };
    }
}

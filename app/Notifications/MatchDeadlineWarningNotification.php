<?php

namespace App\Notifications;

use App\Models\TournamentMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MatchDeadlineWarningNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
        public function __construct(
        public TournamentMatch $match,
        public int $minutesRemaining
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Support pour database (stockage), broadcast (websocket), et possibilité d'ajouter fcm (Firebase)
        return ['database', 'broadcast'];
    }

    /**
     * Get the database representation of the notification (stockée dans la table notifications).
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'match_deadline_warning',
            'match_id' => $this->match->id,
            'tournament_id' => $this->match->tournament_id,
            'tournament_name' => $this->match->tournament->name,
            'round_name' => $this->match->round->round_name ?? "Round {$this->match->round->round_number}",
            'minutes_remaining' => $this->minutesRemaining,
            'deadline_at' => $this->match->deadline_at->toIso8601String(),
            'message' => "Votre match expire dans {$this->minutesRemaining} minutes! Soumettez votre résultat avant {$this->match->deadline_at->format('H:i')}",
            'action_url' => "/matches/{$this->match->id}",
        ];
    }

    /**
     * Get the broadcast representation of the notification (envoyé via websocket).
     *
     * @return array<string, mixed>
     */
    public function toBroadcast(object $notifiable): array
    {
        return [
            'type' => 'match_deadline_warning',
            'match_id' => $this->match->id,
            'tournament_name' => $this->match->tournament->name,
            'minutes_remaining' => $this->minutesRemaining,
            'message' => "⏰ Votre match expire dans {$this->minutesRemaining} minutes!",
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}

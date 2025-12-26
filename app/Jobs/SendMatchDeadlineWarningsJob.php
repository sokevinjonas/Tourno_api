<?php

namespace App\Jobs;

use App\Mail\MatchDeadlineWarningMail;
use App\Models\TournamentMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMatchDeadlineWarningsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job - Envoie des avertissements 1h avant la deadline
     */
    public function handle(): void
    {
        // Trouver tous les matchs qui expirent dans 1h (entre 55 et 65 minutes)
        // et qui n'ont pas encore reçu d'avertissement
        $upcomingMatches = TournamentMatch::whereNotNull('deadline_at')
            ->whereNull('deadline_warning_sent_at')
            ->where('deadline_at', '>', now()->addMinutes(55))
            ->where('deadline_at', '<=', now()->addMinutes(65))
            ->whereNotIn('status', ['completed', 'disputed', 'expired'])
            ->with(['tournament', 'round', 'player1', 'player2', 'matchResults'])
            ->get();

        foreach ($upcomingMatches as $match) {
            try {
                $this->sendWarningEmails($match);
            } catch (\Exception $e) {
                Log::error("Failed to send deadline warning for match {$match->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Envoyer les emails d'avertissement aux joueurs qui n'ont pas soumis
     */
    private function sendWarningEmails(TournamentMatch $match): void
    {
        $submissionsCount = $match->matchResults->count();
        $hoursRemaining = 1; // 1 heure avant deadline

        // Vérifier quels joueurs ont déjà soumis
        $player1Submitted = $match->matchResults->where('submitted_by', $match->player1_id)->isNotEmpty();
        $player2Submitted = $match->matchResults->where('submitted_by', $match->player2_id)->isNotEmpty();

        // Envoyer l'email et notification push seulement aux joueurs qui n'ont PAS soumis
        if (!$player1Submitted) {
            // Email
            Mail::to($match->player1)->send(
                new MatchDeadlineWarningMail($match, $match->player1, $match->player2, $hoursRemaining)
            );

            // Notification push (database + broadcast)
            $match->player1->notify(new \App\Notifications\MatchDeadlineWarningNotification($match, $hoursRemaining));

            Log::info("Deadline warning (email + push) sent to Player 1 (User {$match->player1_id}) for match {$match->id}");
        }

        if (!$player2Submitted) {
            // Email
            Mail::to($match->player2)->send(
                new MatchDeadlineWarningMail($match, $match->player2, $match->player1, $hoursRemaining)
            );

            // Notification push (database + broadcast)
            $match->player2->notify(new \App\Notifications\MatchDeadlineWarningNotification($match, $hoursRemaining));

            Log::info("Deadline warning (email + push) sent to Player 2 (User {$match->player2_id}) for match {$match->id}");
        }

        // Marquer que l'avertissement a été envoyé
        $match->update(['deadline_warning_sent_at' => now()]);
    }
}

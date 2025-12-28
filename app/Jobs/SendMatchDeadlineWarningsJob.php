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
     * Execute the job - Envoie des avertissements 30min et 15min avant la deadline
     */
    public function handle(): void
    {
        // Avertissement 30 minutes avant (fenêtre: 28-32 minutes)
        $matches30min = TournamentMatch::whereNotNull('deadline_at')
            ->whereNull('deadline_warning_30min_sent_at')
            ->where('deadline_at', '>', now()->addMinutes(28))
            ->where('deadline_at', '<=', now()->addMinutes(32))
            ->whereNotIn('status', ['completed', 'disputed', 'expired'])
            ->with(['tournament', 'round', 'player1', 'player2', 'matchResults'])
            ->get();

        foreach ($matches30min as $match) {
            try {
                $this->sendWarningEmails($match, 30, 'deadline_warning_30min_sent_at');
            } catch (\Exception $e) {
                Log::error("Failed to send 30min deadline warning for match {$match->id}: {$e->getMessage()}");
            }
        }

        // Avertissement 15 minutes avant (fenêtre: 13-17 minutes)
        $matches15min = TournamentMatch::whereNotNull('deadline_at')
            ->whereNull('deadline_warning_15min_sent_at')
            ->where('deadline_at', '>', now()->addMinutes(13))
            ->where('deadline_at', '<=', now()->addMinutes(17))
            ->whereNotIn('status', ['completed', 'disputed', 'expired'])
            ->with(['tournament', 'round', 'player1', 'player2', 'matchResults'])
            ->get();

        foreach ($matches15min as $match) {
            try {
                $this->sendWarningEmails($match, 15, 'deadline_warning_15min_sent_at');
            } catch (\Exception $e) {
                Log::error("Failed to send 15min deadline warning for match {$match->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Envoyer les emails d'avertissement aux joueurs qui n'ont pas soumis
     */
    private function sendWarningEmails(TournamentMatch $match, int $minutesRemaining, string $fieldToUpdate): void
    {
        $submissionsCount = $match->matchResults->count();

        // Vérifier quels joueurs ont déjà soumis
        $player1Submitted = $match->matchResults->where('submitted_by', $match->player1_id)->isNotEmpty();
        $player2Submitted = $match->matchResults->where('submitted_by', $match->player2_id)->isNotEmpty();

        // Envoyer l'email et notification push seulement aux joueurs qui n'ont PAS soumis
        if (!$player1Submitted) {
            // Email
            Mail::to($match->player1)->send(
                new MatchDeadlineWarningMail($match, $match->player1, $match->player2, $minutesRemaining)
            );

            // Notification push (database + broadcast)
            $match->player1->notify(new \App\Notifications\MatchDeadlineWarningNotification($match, $minutesRemaining));

            Log::info("Deadline warning {$minutesRemaining}min (email + push) sent to Player 1 (User {$match->player1_id}) for match {$match->id}");
        }

        if (!$player2Submitted) {
            // Email
            Mail::to($match->player2)->send(
                new MatchDeadlineWarningMail($match, $match->player2, $match->player1, $minutesRemaining)
            );

            // Notification push (database + broadcast)
            $match->player2->notify(new \App\Notifications\MatchDeadlineWarningNotification($match, $minutesRemaining));

            Log::info("Deadline warning {$minutesRemaining}min (email + push) sent to Player 2 (User {$match->player2_id}) for match {$match->id}");
        }

        // Marquer que l'avertissement a été envoyé
        $match->update([$fieldToUpdate => now()]);
    }
}

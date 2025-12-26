<?php

namespace App\Jobs;

use App\Models\TournamentMatch;
use App\Models\TournamentRegistration;
use App\Services\KnockoutFormatService;
use App\Services\SwissFormatService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckMatchDeadlinesJob implements ShouldQueue
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
     * Execute the job.
     */
    public function handle(): void
    {
        // Find all matches that have passed their deadline and are not completed
        $expiredMatches = TournamentMatch::whereNotNull('deadline_at')
            ->where('deadline_at', '<=', now())
            ->whereNotIn('status', ['completed', 'disputed', 'expired'])
            ->with(['tournament', 'round', 'matchResults'])
            ->get();

        foreach ($expiredMatches as $match) {
            try {
                $this->handleExpiredMatch($match);
            } catch (\Exception $e) {
                Log::error("Failed to handle expired match {$match->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Handle an expired match based on number of submissions
     */
    private function handleExpiredMatch(TournamentMatch $match): void
    {
        // PROTECTION: Ne jamais toucher un match déjà traité par l'organisateur
        if ($this->isAlreadyProcessedByOrganizer($match)) {
            Log::info("Match {$match->id} - Already processed by organizer, skipping automated processing");
            return;
        }

        $submissionsCount = $match->matchResults->count();

        if ($submissionsCount === 0) {
            // Aucun joueur n'a soumis
            $this->handleNoSubmissions($match);
        } elseif ($submissionsCount === 1) {
            // Un seul joueur a soumis → Il gagne par forfait
            $this->handleOneSubmission($match);
        } else {
            // 2 soumissions → Vérifier la cohérence
            $this->handleTwoSubmissions($match);
        }
    }

    /**
     * Vérifier si le match a déjà été traité par l'organisateur
     */
    private function isAlreadyProcessedByOrganizer(TournamentMatch $match): bool
    {
        // Si les scores sont déjà renseignés (pas NULL), c'est que l'organisateur les a entrés
        return !is_null($match->player1_score) && !is_null($match->player2_score);
    }

    /**
     * Aucun joueur n'a soumis
     */
    private function handleNoSubmissions(TournamentMatch $match): void
    {
        $tournament = $match->tournament;
        $format = $tournament->format;

        if ($format === 'swiss') {
            // Format Suisse → Match nul 0-0
            $this->processDrawResult($match);
            Log::info("Match {$match->id} (Swiss) - No submissions → Draw 0-0");
        } elseif ($format === 'knockout') {
            // Format Knockout → Vérifier si c'est la finale
            $isFinal = $this->isFinalMatch($match);

            if ($isFinal) {
                // Cas spécial: Finale → Prolonger ou annuler
                $this->handleFinalNoSubmission($match);
            } else {
                // Disqualifier les deux joueurs
                $this->disqualifyBothPlayers($match);
                Log::warning("Match {$match->id} (Knockout) - No submissions → Both players disqualified");
            }
        }
    }

    /**
     * Un seul joueur a soumis → Il gagne par forfait
     */
    private function handleOneSubmission(TournamentMatch $match): void
    {
        $submission = $match->matchResults->first();
        $winnerId = $submission->submitted_by;

        // Déterminer les scores (le gagnant obtient son score, le perdant 0)
        if ($winnerId === $match->player1_id) {
            $player1Score = $submission->own_score;
            $player2Score = 0; // Forfait
        } else {
            $player1Score = 0; // Forfait
            $player2Score = $submission->own_score;
        }

        // Utiliser le service approprié pour mettre à jour le résultat
        $this->updateMatchResultViaService($match, $player1Score, $player2Score);

        Log::info("Match {$match->id} - One submission → Winner by forfeit: User {$winnerId}");
    }

    /**
     * Traiter un match nul (format Suisse)
     */
    private function processDrawResult(TournamentMatch $match): void
    {
        $swissService = app(SwissFormatService::class);
        $swissService->updateMatchResult($match, 0, 0);
    }

    /**
     * Mettre à jour le résultat via le service approprié
     */
    private function updateMatchResultViaService(TournamentMatch $match, int $player1Score, int $player2Score): void
    {
        $format = $match->tournament->format;

        if ($format === 'swiss') {
            $service = app(SwissFormatService::class);
        } else {
            $service = app(KnockoutFormatService::class);
        }

        $service->updateMatchResult($match, $player1Score, $player2Score);
    }

    /**
     * Disqualifier les deux joueurs (format Knockout)
     */
    private function disqualifyBothPlayers(TournamentMatch $match): void
    {
        DB::transaction(function () use ($match) {
            // Marquer le match comme expiré sans gagnant
            $match->update([
                'status' => 'expired',
                'winner_id' => null,
                'player1_score' => 0,
                'player2_score' => 0,
                'completed_at' => now(),
            ]);

            // Disqualifier les deux joueurs dans leurs registrations
            $roundName = $match->round->round_name ?? "Round {$match->round->round_number}";

            TournamentRegistration::where('tournament_id', $match->tournament_id)
                ->whereIn('user_id', [$match->player1_id, $match->player2_id])
                ->update([
                    'status' => 'disqualified',
                    'eliminated' => true,
                    'eliminated_round' => $roundName,
                    'eliminated_at' => now(),
                ]);
        });
    }

    /**
     * Gérer le cas spécial de la finale sans soumissions
     */
    private function handleFinalNoSubmission(TournamentMatch $match): void
    {
        // Vérifier si c'est la première expiration
        if (!$match->deadline_extended) {
            // Première expiration → Prolonger de 24h
            $this->extendFinalDeadline($match);
        } else {
            // Deuxième expiration → Annuler le tournoi
            $this->cancelTournamentDueToFinalExpiry($match);
        }
    }

    /**
     * Prolonger la deadline de la finale de 24h
     */
    private function extendFinalDeadline(TournamentMatch $match): void
    {
        DB::transaction(function () use ($match) {
            $newDeadline = now()->addHours(24);

            // Mettre à jour la deadline
            $match->update([
                'deadline_at' => $newDeadline,
                'deadline_extended' => true,
            ]);

            // Charger les joueurs
            $player1 = $match->player1;
            $player2 = $match->player2;

            // Envoyer emails urgents aux deux finalistes
            \Mail::to($player1)->send(
                new \App\Mail\FinalMatchDeadlineExtendedMail($match, $player1, $player2, $newDeadline)
            );

            \Mail::to($player2)->send(
                new \App\Mail\FinalMatchDeadlineExtendedMail($match, $player2, $player1, $newDeadline)
            );

            // Envoyer email à l'organisateur
            \Mail::to($match->tournament->organizer)->send(
                new \App\Mail\FinalMatchDeadlineExtendedMail(
                    $match,
                    $match->tournament->organizer,
                    $player1,
                    $newDeadline
                )
            );

            Log::warning("FINAL Match {$match->id} deadline extended by 24h. New deadline: {$newDeadline}");
        });
    }

    /**
     * Annuler le tournoi car la finale a expiré deux fois
     */
    private function cancelTournamentDueToFinalExpiry(TournamentMatch $match): void
    {
        DB::transaction(function () use ($match) {
            // Marquer le match comme expiré
            $this->markAsExpired($match);

            // Disqualifier les deux finalistes
            TournamentRegistration::where('tournament_id', $match->tournament_id)
                ->whereIn('user_id', [$match->player1_id, $match->player2_id])
                ->update([
                    'status' => 'disqualified',
                    'eliminated' => true,
                    'eliminated_round' => 'Final',
                    'eliminated_at' => now(),
                ]);

            // Annuler le tournoi
            $match->tournament->update(['status' => 'cancelled']);

            Log::critical("Tournament {$match->tournament_id} CANCELLED - Final match {$match->id} expired twice without submissions");

            // TODO: Envoyer email d'annulation à tous les participants
        });
    }

    /**
     * Marquer un match comme expiré sans autre action
     */
    private function markAsExpired(TournamentMatch $match): void
    {
        $match->update([
            'status' => 'expired',
            'winner_id' => null,
            'player1_score' => null,
            'player2_score' => null,
            'completed_at' => now(),
        ]);
    }

    /**
     * Vérifier si c'est un match de finale
     */
    private function isFinalMatch(TournamentMatch $match): bool
    {
        $roundName = strtolower($match->round->round_name ?? '');
        return str_contains($roundName, 'final') && !str_contains($roundName, 'semi');
    }

    /**
     * Gérer le cas où les deux joueurs ont soumis → Vérifier cohérence
     */
    private function handleTwoSubmissions(TournamentMatch $match): void
    {
        $submissions = $match->matchResults;
        $submission1 = $submissions->where('submitted_by', $match->player1_id)->first();
        $submission2 = $submissions->where('submitted_by', $match->player2_id)->first();

        // Scores déclarés par chaque joueur
        $player1DeclaredScore = $submission1->own_score;
        $player1DeclaredOpponentScore = $submission1->opponent_score;

        $player2DeclaredScore = $submission2->own_score;
        $player2DeclaredOpponentScore = $submission2->opponent_score;

        // Vérifier la cohérence
        $isCoherent = (
            $player1DeclaredScore === $player2DeclaredOpponentScore &&
            $player1DeclaredOpponentScore === $player2DeclaredScore
        );

        if ($isCoherent) {
            // Les scores correspondent → Valider automatiquement
            $this->updateMatchResultViaService($match, $player1DeclaredScore, $player2DeclaredScore);

            Log::info("Match {$match->id} - Two submissions COHERENT → Score validated: {$player1DeclaredScore}-{$player2DeclaredScore}");
        } else {
            // CONTRADICTION → Marquer comme disputé et alerter l'organisateur
            $this->handleContradiction($match, $submission1, $submission2);
        }
    }

    /**
     * Gérer une contradiction entre deux soumissions
     */
    private function handleContradiction(TournamentMatch $match, $submission1, $submission2): void
    {
        // Marquer le match comme disputé
        $match->update(['status' => 'disputed']);

        // Envoyer email à l'organisateur
        $organizer = $match->tournament->organizer;

        \Mail::to($organizer)->send(
            new \App\Mail\MatchResultContradictionMail($match, $submission1, $submission2)
        );

        Log::warning("Match {$match->id} - CONTRADICTION detected. Organizer notified. P1: {$submission1->own_score}-{$submission1->opponent_score}, P2: {$submission2->own_score}-{$submission2->opponent_score}");
    }
}

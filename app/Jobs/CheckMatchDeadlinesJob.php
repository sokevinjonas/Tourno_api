<?php

namespace App\Jobs;

use App\Models\TournamentMatch;
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
            ->get();

        foreach ($expiredMatches as $match) {
            try {
                $this->handleExpiredMatch($match);
                Log::info("Match {$match->id} marked as expired - No result submitted before deadline");
            } catch (\Exception $e) {
                Log::error("Failed to handle expired match {$match->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Handle an expired match (both players lose)
     */
    private function handleExpiredMatch(TournamentMatch $match): void
    {
        DB::transaction(function () use ($match) {
            // Mark match as expired with no winner
            $match->update([
                'status' => 'expired',
                'winner_id' => null, // No winner
                'player1_score' => null,
                'player2_score' => null,
                'completed_at' => now(),
            ]);

            Log::warning("Match {$match->id} expired. Tournament: {$match->tournament_id}, Round: {$match->round_id}");
        });
    }
}

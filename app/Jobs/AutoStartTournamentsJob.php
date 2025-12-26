<?php

namespace App\Jobs;

use App\Models\Tournament;
use App\Services\TournamentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoStartTournamentsJob implements ShouldQueue
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
    public function handle(TournamentService $tournamentService): void
    {
        // Trouver les tournois auto-managed qui doivent démarrer
        // Un tournoi démarre si :
        // 1. Il est auto-managed
        // 2. Il est en status 'open'
        // 3. La date de début est passée
        // 4. Le tournoi est COMPLET (inscriptions = max_participants)
        $tournaments = Tournament::where('auto_managed', true)
            ->where('status', 'open')
            ->where('start_date', '<=', now())
            ->withCount(['registrations' => function ($query) {
                $query->where('status', 'registered');
            }])
            ->get()
            ->filter(function ($tournament) {
                return $tournament->registrations_count >= $tournament->max_participants;
            });

        foreach ($tournaments as $tournament) {
            try {
                $tournamentService->autoStartTournament($tournament);
                Log::info("Auto-started tournament: {$tournament->id} - {$tournament->name}");
            } catch (\Exception $e) {
                Log::error("Failed to auto-start tournament {$tournament->id}: {$e->getMessage()}");
            }
        }
    }
}

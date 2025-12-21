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
        $tournaments = Tournament::where('auto_managed', true)
            ->where('status', 'open')
            ->withCount('registrations')
            ->having('registrations_count', '>=', \DB::raw('max_participants'))
            ->where('start_date', '<=', now())
            ->get();

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

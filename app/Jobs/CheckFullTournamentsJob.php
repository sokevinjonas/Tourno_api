<?php

namespace App\Jobs;

use App\Models\Tournament;
use App\Mail\TournamentRefundMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckFullTournamentsJob implements ShouldQueue
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
        // Trouver tous les tournois pleins depuis plus de 48h et pas démarrés
        $tournaments = Tournament::where('status', 'open')
            ->whereNotNull('full_since')
            ->where('full_since', '<=', now()->subHours(48))
            ->whereNull('actual_start_date')
            ->get();

        foreach ($tournaments as $tournament) {
            try {
                $this->refundTournamentParticipants($tournament);
                Log::info("Refunded tournament: {$tournament->id} - {$tournament->name}");
            } catch (\Exception $e) {
                Log::error("Failed to refund tournament {$tournament->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Rembourser tous les participants du tournoi
     */
    private function refundTournamentParticipants(Tournament $tournament): void
    {
        DB::transaction(function () use ($tournament) {
            foreach ($tournament->registrations as $registration) {
                $user = $registration->user;

                // Rembourser l'entry fee
                $balanceBefore = $user->wallet->balance;
                $user->wallet->balance += $tournament->entry_fee;
                $user->wallet->save();

                // Créer transaction
                DB::table('transactions')->insert([
                    'wallet_id' => $user->wallet->id,
                    'user_id' => $user->id,
                    'type' => 'credit',
                    'amount' => $tournament->entry_fee,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $user->wallet->balance,
                    'reason' => 'refund',
                    'description' => "Automatic refund for tournament not started: {$tournament->name}",
                    'tournament_id' => $tournament->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Envoyer email de notification
                Mail::to($user)->send(new TournamentRefundMail($tournament, $user));
            }

            // Annuler le tournoi
            $tournament->update(['status' => 'cancelled']);
        });
    }
}

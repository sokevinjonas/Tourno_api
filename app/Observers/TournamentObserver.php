<?php

namespace App\Observers;

use App\Models\Tournament;
use App\Models\TournamentWalletLock;

class TournamentObserver
{
    /**
     * Handle the Tournament "created" event.
     */
    public function created(Tournament $tournament): void
    {
        // Créer automatiquement le wallet lock pour ce tournoi
        // avec locked_amount = 0, qui sera incrémenté à chaque inscription
        TournamentWalletLock::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'tournament_id' => $tournament->id,
            'organizer_id' => $tournament->organizer_id,
            'wallet_id' => $tournament->organizer->wallet->id,
            'locked_amount' => 0,
            'status' => 'locked',
        ]);
    }
}

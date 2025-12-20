<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TournamentService
{
    /**
     * Create a new tournament
     */
    public function createTournament(User $organizer, array $data): Tournament
    {
        if (!in_array($organizer->role, ['admin', 'organizer'])) {
            throw new \Exception('Unauthorized: Only admins and organizers can create tournaments');
        }

        return DB::transaction(function () use ($organizer, $data) {
            // Create tournament
            $tournament = Tournament::create([
                'organizer_id' => $organizer->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'game_type' => $data['game_type'],
                'format' => $data['format'] ?? 'swiss',
                'max_participants' => $data['max_participants'],
                'entry_fee' => $data['entry_fee'],
                'prize_pool' => $data['prize_pool'] ?? 0,
                'prize_distribution' => $data['prize_distribution'] ?? null,
                'status' => $data['status'] ?? 'upcoming',
                'registration_start' => $data['registration_start'],
                'registration_end' => $data['registration_end'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'rules' => $data['rules'] ?? null,
            ]);

            return $tournament;
        });
    }

    /**
     * Update a tournament
     */
    public function updateTournament(Tournament $tournament, User $user, array $data): Tournament
    {
        // Only organizer or admin can update
        if ($tournament->organizer_id !== $user->id && $user->role !== 'admin') {
            throw new \Exception('Unauthorized: You can only update your own tournaments');
        }

        // Cannot update if tournament is in progress or completed
        if (in_array($tournament->status, ['in_progress', 'completed'])) {
            throw new \Exception('Cannot update tournament that is in progress or completed');
        }

        $tournament->update($data);

        return $tournament->fresh();
    }

    /**
     * Delete a tournament
     */
    public function deleteTournament(Tournament $tournament, User $user): bool
    {
        // Only organizer or admin can delete
        if ($tournament->organizer_id !== $user->id && $user->role !== 'admin') {
            throw new \Exception('Unauthorized: You can only delete your own tournaments');
        }

        // Cannot delete if tournament has started
        if (in_array($tournament->status, ['in_progress', 'completed'])) {
            throw new \Exception('Cannot delete tournament that is in progress or completed');
        }

        return $tournament->delete();
    }

    /**
     * Get all tournaments with filters
     */
    public function getTournaments(array $filters = [])
    {
        $query = Tournament::with([
            'organizer:id,name,email',
            'registrations.gameAccount',
            'registrations.user:id,name,email'
        ]);

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by game type
        if (isset($filters['game_type'])) {
            $query->where('game_type', $filters['game_type']);
        }

        // Filter by organizer
        if (isset($filters['organizer_id'])) {
            $query->where('organizer_id', $filters['organizer_id']);
        }

        // Order by start date
        $query->orderBy('start_date', $filters['sort'] ?? 'desc');

        $tournaments = $query->get();

        return [
            'tournaments' => $tournaments,
            'total' => $tournaments->count()
        ];
    }

    /**
     * Get tournament by ID
     */
    public function getTournament(int $id): ?Tournament
    {
        return Tournament::with(['organizer:id,name,email', 'registrations.gameAccount', 'registrations.user:id,name,email', 'rounds', 'matches'])
            ->find($id);
    }

    /**
     * Get upcoming tournaments
     */
    public function getUpcomingTournaments(string $gameType = null)
    {
        $query = Tournament::upcoming()
            ->with(['organizer:id,name,email'])
            ->where('start_date', '>', now());

        if ($gameType) {
            $query->where('game_type', $gameType);
        }

        return $query->orderBy('start_date', 'asc')->get();
    }

    /**
     * Get tournaments currently in registration
     */
    public function getRegisteringTournaments(string $gameType = null)
    {
        $query = Tournament::registering()
            ->with(['organizer:id,name,email'])
            ->where('registration_start', '<=', now())
            ->where('registration_end', '>=', now());

        if ($gameType) {
            $query->where('game_type', $gameType);
        }

        return $query->orderBy('registration_end', 'asc')->get();
    }

    /**
     * Change tournament status
     */
    public function changeTournamentStatus(Tournament $tournament, User $user, string $status): Tournament
    {
        // Only organizer or admin can change status
        if ($tournament->organizer_id !== $user->id && $user->role !== 'admin') {
            throw new \Exception('Unauthorized: You can only update your own tournaments');
        }

        $allowedStatuses = ['upcoming', 'registering', 'in_progress', 'completed', 'cancelled'];

        if (!in_array($status, $allowedStatuses)) {
            throw new \Exception("Invalid status: {$status}");
        }

        $tournament->update(['status' => $status]);

        return $tournament->fresh();
    }

    /**
     * Calculate number of rounds for Swiss format
     * Formula: N = ⌈log₂(P)⌉ where P = number of participants
     */
    public function calculateSwissRounds(int $participants): int
    {
        if ($participants <= 1) {
            return 0;
        }

        return (int) ceil(log($participants, 2));
    }

    /**
     * Calculate prize pool based on registrations
     */
    public function calculatePrizePool(Tournament $tournament): float
    {
        $registeredCount = $tournament->registrations()->where('status', 'registered')->count();
        $totalEntryFees = $registeredCount * $tournament->entry_fee;

        // Platform could take a percentage here if needed
        // For now, 100% goes to prize pool
        return $totalEntryFees;
    }

    /**
     * Get tournament statistics
     */
    public function getTournamentStatistics(Tournament $tournament): array
    {
        $registrations = $tournament->registrations()->where('status', 'registered')->count();
        $maxParticipants = $tournament->max_participants;
        $spotsRemaining = $maxParticipants - $registrations;

        return [
            'total_registered' => $registrations,
            'max_participants' => $maxParticipants,
            'spots_remaining' => max(0, $spotsRemaining),
            'is_full' => $registrations >= $maxParticipants,
            'total_rounds' => $this->calculateSwissRounds($registrations),
            'current_prize_pool' => $this->calculatePrizePool($tournament),
        ];
    }

    /**
     * Get organizer tournaments
     */
    public function getOrganizerTournaments(User $organizer)
    {
        return Tournament::where('organizer_id', $organizer->id)
            ->with(['registrations'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Validate tournament data
     */
    public function validateTournamentDates(array $data): void
    {
        $registrationStart = new \DateTime($data['registration_start']);
        $registrationEnd = new \DateTime($data['registration_end']);
        $startDate = new \DateTime($data['start_date']);

        if ($registrationEnd <= $registrationStart) {
            throw new \Exception('Registration end date must be after registration start date');
        }

        if ($startDate <= $registrationEnd) {
            throw new \Exception('Tournament start date must be after registration end date');
        }
    }
}

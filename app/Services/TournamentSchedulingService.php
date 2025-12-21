<?php

namespace App\Services;

use App\Models\Tournament;
use Carbon\Carbon;

class TournamentSchedulingService
{
    /**
     * Calculate recommended tournament duration based on format and participants
     */
    public function calculateRecommendedDuration(string $format, int $maxParticipants): int
    {
        return match ($format) {
            'single_elimination' => $this->calculateEliminationDuration($maxParticipants),
            'swiss' => $this->calculateSwissDuration($maxParticipants),
            'champions_league' => $this->calculateChampionsLeagueDuration($maxParticipants),
            default => 3, // Default 3 days
        };
    }

    /**
     * Calculate duration for Single Elimination (Coupe)
     */
    private function calculateEliminationDuration(int $participants): int
    {
        $rounds = (int) ceil(log($participants, 2));

        // 1 jour par round minimum, max 1 semaine
        return min(max($rounds, 2), 7);
    }

    /**
     * Calculate duration for Swiss System
     */
    private function calculateSwissDuration(int $participants): int
    {
        // Swiss: log2(participants) rounds
        $rounds = (int) ceil(log($participants, 2));

        // 1-2 jours par round
        return min($rounds * 2, 14);
    }

    /**
     * Calculate duration for Champions League format
     */
    private function calculateChampionsLeagueDuration(int $participants): int
    {
        // 16 participants = 4 groupes de 4
        // Phase de groupes: 3 matches par équipe
        // Phase knockout: 3-4 rounds

        if ($participants == 16) {
            return 7; // 3 jours groupes + 4 jours knockout
        }

        return 5; // Default
    }

    /**
     * Calculate number of rounds based on format
     */
    public function calculateTotalRounds(string $format, int $maxParticipants): int
    {
        return match ($format) {
            'single_elimination' => (int) ceil(log($maxParticipants, 2)),
            'swiss' => (int) ceil(log($maxParticipants, 2)),
            'champions_league' => 3 + 3, // 3 rounds groupes + 3 rounds knockout (QF, SF, F)
            default => 3,
        };
    }

    /**
     * Generate match schedule for entire tournament
     */
    public function generateTournamentSchedule(Tournament $tournament): array
    {
        $totalRounds = $this->calculateTotalRounds($tournament->format, $tournament->max_participants);
        $durationDays = $tournament->tournament_duration_days ?? $this->calculateRecommendedDuration($tournament->format, $tournament->max_participants);

        $schedule = [];
        $startDate = Carbon::parse($tournament->start_date);

        // Calculate time per round (in hours)
        $hoursPerRound = ($durationDays * 24) / $totalRounds;

        for ($round = 1; $round <= $totalRounds; $round++) {
            $roundStart = $startDate->copy()->addHours(($round - 1) * $hoursPerRound);
            $roundEnd = $startDate->copy()->addHours($round * $hoursPerRound);

            $matchesInRound = $this->getMatchesCountForRound($tournament->format, $tournament->max_participants, $round);

            $schedule[] = [
                'round_number' => $round,
                'round_name' => $this->getRoundName($tournament->format, $round, $totalRounds),
                'start_date' => $roundStart,
                'end_date' => $roundEnd,
                'matches_count' => $matchesInRound,
                'matches' => $this->generateMatchTimesForRound(
                    $roundStart,
                    $roundEnd,
                    $matchesInRound,
                    $tournament->time_slot,
                    $tournament->match_deadline_minutes ?? 60
                ),
            ];
        }

        return $schedule;
    }

    /**
     * Get number of matches in a specific round
     */
    private function getMatchesCountForRound(string $format, int $maxParticipants, int $roundNumber): int
    {
        if ($format === 'single_elimination') {
            // Round 1: maxParticipants/2, Round 2: maxParticipants/4, etc.
            return (int) ($maxParticipants / pow(2, $roundNumber));
        }

        if ($format === 'swiss') {
            // All participants play each round
            return (int) ($maxParticipants / 2);
        }

        if ($format === 'champions_league') {
            if ($roundNumber <= 3) {
                // Group phase: 8 matches per round (4 groups × 2 matches)
                return 8;
            } else {
                // Knockout: QF=4, SF=2, F=1
                return (int) (8 / pow(2, $roundNumber - 3));
            }
        }

        return 1;
    }

    /**
     * Get round name based on format
     */
    private function getRoundName(string $format, int $roundNumber, int $totalRounds): string
    {
        if ($format === 'single_elimination') {
            $remaining = $totalRounds - $roundNumber + 1;

            return match ($remaining) {
                1 => 'Finale',
                2 => 'Demi-finales',
                3 => 'Quarts de finale',
                4 => 'Huitièmes de finale',
                default => "Tour $roundNumber",
            };
        }

        if ($format === 'champions_league') {
            if ($roundNumber <= 3) {
                return "Phase de groupes - Round $roundNumber";
            }

            $knockoutRound = $roundNumber - 3;
            return match ($knockoutRound) {
                1 => 'Quarts de finale',
                2 => 'Demi-finales',
                3 => 'Finale',
                default => "Tour knockout $knockoutRound",
            };
        }

        return "Round $roundNumber";
    }

    /**
     * Generate match times for a specific round
     */
    private function generateMatchTimesForRound(
        Carbon $roundStart,
        Carbon $roundEnd,
        int $matchesCount,
        string $timeSlot,
        int $deadlineMinutes
    ): array {
        $matches = [];
        $slotHours = $this->getTimeSlotHours($timeSlot);

        // Distribute matches across the round duration
        $roundDurationHours = $roundStart->diffInHours($roundEnd);

        // Start matches at the beginning of the time slot on day 1
        $matchDate = $roundStart->copy()->setTime($slotHours['start'], 0);

        // Space matches 30 minutes apart
        for ($i = 0; $i < $matchesCount; $i++) {
            $scheduledAt = $matchDate->copy()->addMinutes($i * 30);
            $deadlineAt = $scheduledAt->copy()->addMinutes($deadlineMinutes);

            $matches[] = [
                'match_number' => $i + 1,
                'scheduled_at' => $scheduledAt,
                'deadline_at' => $deadlineAt,
            ];
        }

        return $matches;
    }

    /**
     * Get time slot hours
     */
    private function getTimeSlotHours(string $timeSlot): array
    {
        return match ($timeSlot) {
            'morning' => ['start' => 9, 'end' => 12],
            'afternoon' => ['start' => 13, 'end' => 16],
            'evening' => ['start' => 18, 'end' => 23],
            default => ['start' => 18, 'end' => 23],
        };
    }

    /**
     * Preview tournament schedule before creation
     */
    public function previewSchedule(array $tournamentData): array
    {
        $format = $tournamentData['format'];
        $maxParticipants = $tournamentData['max_participants'];
        $startDate = Carbon::parse($tournamentData['start_date']);
        $timeSlot = $tournamentData['time_slot'] ?? 'evening';
        $deadlineMinutes = $tournamentData['match_deadline_minutes'] ?? 60;

        // Calculate recommended duration if not provided
        $durationDays = $tournamentData['tournament_duration_days']
            ?? $this->calculateRecommendedDuration($format, $maxParticipants);

        $totalRounds = $this->calculateTotalRounds($format, $maxParticipants);
        $hoursPerRound = ($durationDays * 24) / $totalRounds;

        $schedule = [];

        for ($round = 1; $round <= $totalRounds; $round++) {
            $roundStart = $startDate->copy()->addHours(($round - 1) * $hoursPerRound);
            $roundEnd = $startDate->copy()->addHours($round * $hoursPerRound);

            $matchesInRound = $this->getMatchesCountForRound($format, $maxParticipants, $round);

            $schedule[] = [
                'round_number' => $round,
                'round_name' => $this->getRoundName($format, $round, $totalRounds),
                'start_date' => $roundStart->toIso8601String(),
                'end_date' => $roundEnd->toIso8601String(),
                'matches_count' => $matchesInRound,
                'sample_match_times' => $this->generateMatchTimesForRound(
                    $roundStart,
                    $roundEnd,
                    min($matchesInRound, 3), // Show max 3 sample matches
                    $timeSlot,
                    $deadlineMinutes
                ),
            ];
        }

        return [
            'format' => $format,
            'max_participants' => $maxParticipants,
            'recommended_duration_days' => $this->calculateRecommendedDuration($format, $maxParticipants),
            'selected_duration_days' => $durationDays,
            'total_rounds' => $totalRounds,
            'time_slot' => $timeSlot,
            'match_deadline_minutes' => $deadlineMinutes,
            'schedule' => $schedule,
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'tournament_id',
        'round_id',
        'player1_id',
        'player2_id',
        'player1_score',
        'player2_score',
        'winner_id',
        'status',
        'scheduled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function round()
    {
        return $this->belongsTo(Round::class);
    }

    public function player1()
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    public function player2()
    {
        return $this->belongsTo(User::class, 'player2_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function matchResults()
    {
        return $this->hasMany(MatchResult::class, 'match_id');
    }

    /**
     * Scopes
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopePendingValidation($query)
    {
        return $query->where('status', 'pending_validation');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeDisputed($query)
    {
        return $query->where('status', 'disputed');
    }
}

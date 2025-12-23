<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'user_id',
        'game_account_id',
        'status',
        'tournament_points',
        'wins',
        'draws',
        'losses',
        'final_rank',
        'prize_won',
    ];

    protected function casts(): array
    {
        return [
            'prize_won' => 'decimal:2',
        ];
    }

    /**
     * Relationships
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gameAccount()
    {
        return $this->belongsTo(GameAccount::class);
    }

    /**
     * Scopes
     */
    public function scopeRegistered($query)
    {
        return $query->where('status', 'registered');
    }

    public function scopeWithdrawn($query)
    {
        return $query->where('status', 'withdrawn');
    }

    public function scopeDisqualified($query)
    {
        return $query->where('status', 'disqualified');
    }
}

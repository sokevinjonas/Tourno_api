<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TournamentRegistration extends Model
{
    use HasFactory;

    /**
     * Boot function to generate UUID on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registration) {
            if (empty($registration->uuid)) {
                $registration->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'id',
        'tournament_id',
        'user_id',
        'game_account_id',
    ];

    protected $fillable = [
        'uuid',
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
        'eliminated',
        'eliminated_round',
        'eliminated_at',
    ];

    protected function casts(): array
    {
        return [
            'prize_won' => 'decimal:2',
            'eliminated' => 'boolean',
            'eliminated_at' => 'datetime',
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

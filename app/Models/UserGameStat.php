<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserGameStat extends Model
{
    use HasFactory;

    /**
     * Boot function to generate UUID on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($stat) {
            if (empty($stat->uuid)) {
                $stat->uuid = (string) Str::uuid();
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
        'user_id',
    ];

    protected $fillable = [
        'uuid',
        'user_id',
        'game',
        'rating_points',
        'tournaments_played',
        'tournaments_won',
        'total_matches_played',
        'total_matches_won',
        'total_matches_lost',
        'total_matches_draw',
        'total_prize_money',
        'last_tournament_at',
    ];

    protected function casts(): array
    {
        return [
            'total_prize_money' => 'decimal:2',
            'last_tournament_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeByGame($query, string $game)
    {
        return $query->where('game', $game);
    }

    public function scopeOrderByRating($query)
    {
        return $query->orderBy('rating_points', 'desc');
    }
}

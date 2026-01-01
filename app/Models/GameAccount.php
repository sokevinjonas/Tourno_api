<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GameAccount extends Model
{
    use HasFactory;

    /**
     * Boot function to generate UUID on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($gameAccount) {
            if (empty($gameAccount->uuid)) {
                $gameAccount->uuid = (string) Str::uuid();
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
        'game_username',
        'team_screenshot_path',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tournamentRegistrations()
    {
        return $this->hasMany(TournamentRegistration::class);
    }

    /**
     * Scopes
     */
    public function scopeByGameType($query, $gameType)
    {
        return $query->where('game_type', $gameType);
    }
}

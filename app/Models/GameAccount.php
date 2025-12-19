<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameAccount extends Model
{
    use HasFactory;

    protected $fillable = [
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

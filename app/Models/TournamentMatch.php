<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TournamentMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    /**
     * Boot function to generate UUID on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($match) {
            if (empty($match->uuid)) {
                $match->uuid = (string) Str::uuid();
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
        'round_id',
        'player1_id',
        'player2_id',
        'winner_id',
        'next_match_id',
    ];

    protected $fillable = [
        'uuid',
        'tournament_id',
        'round_id',
        'player1_id',
        'player2_id',
        'player1_score',
        'player2_score',
        'winner_id',
        'status',
        'scheduled_at',
        'deadline_at',
        'deadline_extended',
        'deadline_warning_30min_sent_at',
        'deadline_warning_15min_sent_at',
        'completed_at',
        'next_match_id',
        'bracket_position',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'deadline_at' => 'datetime',
            'deadline_extended' => 'boolean',
            'deadline_warning_30min_sent_at' => 'datetime',
            'deadline_warning_15min_sent_at' => 'datetime',
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

    public function messages()
    {
        return $this->hasMany(MatchMessage::class, 'match_id');
    }

    public function evidence()
    {
        return $this->hasMany(MatchEvidence::class, 'match_id');
    }

    public function nextMatch()
    {
        return $this->belongsTo(TournamentMatch::class, 'next_match_id');
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

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }
}

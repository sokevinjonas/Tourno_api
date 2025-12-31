<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'name',
        'description',
        'game',
        'format',
        'max_participants',
        'entry_fee',
        'prize_distribution',
        'rules',
        'status',
        'visibility',
        'unique_url',
        'creation_fee_paid',
        'full_since',
        'auto_managed',
        'start_date',
        'actual_start_date',
        'tournament_duration_days',
        'time_slot',
        'match_deadline_minutes',
        'total_rounds',
        'current_round',
    ];

    protected function casts(): array
    {
        return [
            'entry_fee' => 'decimal:2',
            'creation_fee_paid' => 'decimal:2',
            'prize_distribution' => 'json',
            'rules' => 'json',
            'auto_managed' => 'boolean',
            'start_date' => 'datetime',
            'actual_start_date' => 'datetime',
            'full_since' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function registrations()
    {
        return $this->hasMany(TournamentRegistration::class);
    }

    public function rounds()
    {
        return $this->hasMany(Round::class);
    }

    public function matches()
    {
        return $this->hasMany(TournamentMatch::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scopes
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeRegistering($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByGame($query, $game)
    {
        return $query->where('game', $game);
    }

    public function scopeByFormat($query, $format)
    {
        return $query->where('format', $format);
    }
}

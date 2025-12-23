<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchEvidence extends Model
{
    use HasFactory;

    protected $table = 'match_evidence';

    protected $fillable = [
        'match_id',
        'user_id',
        'file_path',
        'type',
        'description',
    ];

    /**
     * Relationships
     */
    public function match()
    {
        return $this->belongsTo(TournamentMatch::class, 'match_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeForMatch($query, $matchId)
    {
        return $query->where('match_id', $matchId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}

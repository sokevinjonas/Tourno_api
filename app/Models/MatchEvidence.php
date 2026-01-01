<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MatchEvidence extends Model
{
    use HasFactory;

    protected $table = 'match_evidence';

    /**
     * Boot function to generate UUID on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($evidence) {
            if (empty($evidence->uuid)) {
                $evidence->uuid = (string) Str::uuid();
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
        'match_id',
        'user_id',
    ];

    protected $fillable = [
        'uuid',
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

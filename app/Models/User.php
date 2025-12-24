<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar_url',
        'role',
        'is_banned',
        'banned_until',
        'ban_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'banned_until' => 'datetime',
            'is_banned' => 'boolean',
        ];
    }

    /**
     * Relationships
     */
    public function oauthProviders()
    {
        return $this->hasMany(OAuthProvider::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function gameAccounts()
    {
        return $this->hasMany(GameAccount::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function tournamentRegistrations()
    {
        return $this->hasMany(TournamentRegistration::class);
    }

    public function tournaments()
    {
        return $this->hasMany(Tournament::class, 'organizer_id');
    }

    public function registrations()
    {
        return $this->hasMany(TournamentRegistration::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function player1Matches()
    {
        return $this->hasMany(TournamentMatch::class, 'player1_id');
    }

    public function player2Matches()
    {
        return $this->hasMany(TournamentMatch::class, 'player2_id');
    }

    public function wonMatches()
    {
        return $this->hasMany(TournamentMatch::class, 'winner_id');
    }

    public function submittedMatchResults()
    {
        return $this->hasMany(MatchResult::class, 'submitted_by');
    }

    public function organizerProfile()
    {
        return $this->hasOne(OrganizerProfile::class);
    }

    // Les organisateurs que cet utilisateur suit
    public function followingOrganizers()
    {
        return $this->belongsToMany(User::class, 'organizer_followers', 'user_id', 'organizer_id')
            ->withTimestamps();
    }

    // Les followers de cet organisateur (si l'utilisateur est un organisateur)
    public function followers()
    {
        return $this->belongsToMany(User::class, 'organizer_followers', 'organizer_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeModerators($query)
    {
        return $query->where('role', 'moderator');
    }

    public function scopeOrganizers($query)
    {
        return $query->where('role', 'organizer');
    }

    public function scopePlayers($query)
    {
        return $query->where('role', 'player');
    }

    public function scopeNotBanned($query)
    {
        return $query->where('is_banned', false);
    }
}

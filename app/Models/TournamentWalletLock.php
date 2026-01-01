<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TournamentWalletLock extends Model
{
    use HasFactory;

    /**
     * Boot function to generate UUID on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lock) {
            if (empty($lock->uuid)) {
                $lock->uuid = (string) Str::uuid();
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
        'organizer_id',
        'wallet_id',
    ];

    protected $fillable = [
        'uuid',
        'tournament_id',
        'organizer_id',
        'wallet_id',
        'locked_amount',
        'locked_prizes',
        'status',
        'paid_out',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'locked_amount' => 'decimal:2',
            'locked_prizes' => 'decimal:2',
            'paid_out' => 'decimal:2',
            'released_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}

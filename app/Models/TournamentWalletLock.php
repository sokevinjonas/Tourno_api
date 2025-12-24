<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentWalletLock extends Model
{
    use HasFactory;

    protected $fillable = [
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

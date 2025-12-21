<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentWalletLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'wallet_id',
        'locked_amount',
        'status',
        'paid_out',
    ];

    protected function casts(): array
    {
        return [
            'locked_amount' => 'decimal:2',
            'paid_out' => 'decimal:2',
        ];
    }

    /**
     * Relationships
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}

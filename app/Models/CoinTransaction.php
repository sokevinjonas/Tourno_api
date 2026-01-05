<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CoinTransaction extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'type',
        'amount_coins',
        'amount_money',
        'fee_percentage',
        'fee_amount',
        'net_amount',
        'currency',
        'payment_method',
        'payment_provider',
        'payment_phone',
        'fusionpay_token',
        'fusionpay_transaction_number',
        'fusionpay_event',
        'proof_screenshot',
        'admin_note',
        'processed_by',
        'processed_at',
        'rejection_reason',
        'status',
    ];

    protected $casts = [
        'amount_coins' => 'decimal:2',
        'amount_money' => 'decimal:2',
        'fee_percentage' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->uuid)) {
                $transaction->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec l'admin/moderator qui a traité
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope pour les dépôts
     */
    public function scopeDeposits($query)
    {
        return $query->where('type', 'deposit');
    }

    /**
     * Scope pour les retraits
     */
    public function scopeWithdrawals($query)
    {
        return $query->where('type', 'withdrawal');
    }

    /**
     * Scope pour les transactions en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope pour les transactions complétées
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Vérifier si la transaction est un dépôt
     */
    public function isDeposit(): bool
    {
        return $this->type === 'deposit';
    }

    /**
     * Vérifier si la transaction est un retrait
     */
    public function isWithdrawal(): bool
    {
        return $this->type === 'withdrawal';
    }

    /**
     * Vérifier si la transaction est complétée
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Vérifier si la transaction est en attente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}

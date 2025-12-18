<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'whatsapp_number',
        'country',
        'city',
        'date_of_birth',
        'bio',
        'validation_status',
        'validated_by',
        'validated_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'validated_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('validation_status', 'pending');
    }

    public function scopeValidated($query)
    {
        return $query->where('validation_status', 'validated');
    }

    public function scopeRejected($query)
    {
        return $query->where('validation_status', 'rejected');
    }
}

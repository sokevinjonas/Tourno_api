<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrganizerProfile extends Model
{
    use HasFactory;

    /**
     * Boot function to generate UUID on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($profile) {
            if (empty($profile->uuid)) {
                $profile->uuid = (string) Str::uuid();
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
        'user_id',
        'processed_by_user_id',
    ];

    protected $fillable = [
        'uuid',
        'user_id',
        'display_name',
        'badge',
        'avatar_url',
        'avatar_initial',
        'bio',
        'social_links',
        'is_featured',
        'nature_document',
        'doc_recto',
        'doc_verso',
        'status',
        'contrat_signer',
        'rejection_reason',
        'processed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    /**
     * Scopes
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeCertified($query)
    {
        return $query->where('badge', 'certified');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'attente');
    }

    public function scopeValidated($query)
    {
        return $query->where('status', 'valider');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejeter');
    }
}

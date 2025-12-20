<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
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

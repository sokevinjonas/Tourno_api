<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Initialize the trait for an instance.
     */
    public function initializeHasUuid()
    {
        // Generate UUID if not set
        if (empty($this->attributes['uuid'])) {
            $this->attributes['uuid'] = (string) Str::uuid();
        }
    }

    /**
     * Boot the trait.
     */
    protected static function bootHasUuid()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
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
}

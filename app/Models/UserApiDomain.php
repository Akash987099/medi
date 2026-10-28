<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserApiDomain extends Model
{
    protected $fillable = [
        'user_id',
        'domain',
        'domain_hash',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $apiDomain): void {
            $apiDomain->domain_hash ??= hash('sha256', $apiDomain->domain);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

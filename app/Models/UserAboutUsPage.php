<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAboutUsPage extends Model
{
    protected $fillable = [
        'hero_title',
        'intro',
        'hero_image_path',
        'sections',
    ];

    protected $casts = [
        'sections' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getHeroImageUrlAttribute(): ?string
    {
        return $this->hero_image_path ? asset($this->hero_image_path) : null;
    }
}

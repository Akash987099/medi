<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UserArticle extends Model
{
    protected $fillable = [
        'title',
        'content',
        'image_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute(): string
    {
        if (str_starts_with($this->image_path, 'user-articles/')) {
            return Storage::disk('public')->url($this->image_path);
        }

        return asset($this->image_path);
    }
}

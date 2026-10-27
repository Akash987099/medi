<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UserNews extends Model
{
    protected $table = 'user_news';

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
        if (str_starts_with($this->image_path, 'user-news/')) {
            return Storage::disk('public')->url($this->image_path);
        }

        return asset($this->image_path);
    }
}

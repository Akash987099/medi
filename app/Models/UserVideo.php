<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UserVideo extends Model
{
    protected $fillable = [
        'title',
        'caption',
        'video_type',
        'video_path',
        'youtube_video_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getVideoUrlAttribute(): ?string
    {
        if ($this->video_type !== 'upload' || ! $this->video_path) {
            return null;
        }

        if (str_starts_with($this->video_path, 'user-videos/')) {
            return Storage::disk('public')->url($this->video_path);
        }

        return route('user.videos.stream', $this->id);
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        return $this->video_type === 'youtube' && $this->youtube_video_id
            ? 'https://www.youtube-nocookie.com/embed/'.$this->youtube_video_id
            : null;
    }
}

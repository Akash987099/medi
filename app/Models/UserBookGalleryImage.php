<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBookGalleryImage extends Model
{
    protected $fillable = ['user_id', 'image_path', 'caption'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(UserBook::class, 'user_book_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

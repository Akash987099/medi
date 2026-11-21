<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserBook extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'authors', 'publisher', 'isbn', 'language', 'publication_date',
        'format', 'description', 'inventory_quantity', 'price', 'cover_path', 'ebook_pdf_path', 'is_active',
    ];

    protected $casts = [
        'publication_date' => 'date',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(UserBookGalleryImage::class, 'user_book_id');
    }
}

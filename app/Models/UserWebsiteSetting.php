<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWebsiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'tagline',
        'contact_email',
        'phone',
        'whatsapp',
        'website_url',
        'address',
        'header_logo_path',
        'footer_logo_path',
        'footer_text',
        'copyright_text',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getHeaderLogoUrlAttribute(): ?string
    {
        return $this->header_logo_path ? asset($this->header_logo_path) : null;
    }

    public function getFooterLogoUrlAttribute(): ?string
    {
        return $this->footer_logo_path ? asset($this->footer_logo_path) : null;
    }
}

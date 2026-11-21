<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPanelSetting extends Model
{
    public const DEFAULTS = [
        'primary_color' => '#54745d', 'background_color' => '#f7f6f1', 'text_color' => '#26382f',
        'accent_color' => '#bd795e', 'sidebar_color' => '#ffffff', 'panel_color' => '#fffefa',
    ];

    protected $fillable = ['primary_color', 'background_color', 'text_color', 'accent_color', 'sidebar_color', 'panel_color'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAiMessage extends Model
{
    protected $fillable = ['role', 'content'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(UserAiConversation::class, 'conversation_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPatient extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'age', 'gender', 'blood_group', 'address', 'notes', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'age' => 'integer'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

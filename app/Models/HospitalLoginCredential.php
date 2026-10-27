<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalLoginCredential extends Model
{
    use HasFactory;

    protected $fillable = ['hospital_id', 'user_id', 'username', 'password'];

    protected $hidden = ['password'];

    protected $casts = [
        'password' => 'encrypted',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

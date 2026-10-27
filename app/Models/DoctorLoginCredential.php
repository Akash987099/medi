<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorLoginCredential extends Model
{
    use HasFactory;

    protected $fillable = ['doctor_id', 'user_id', 'username', 'password'];

    protected $hidden = ['password'];

    protected $casts = [
        'password' => 'encrypted',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

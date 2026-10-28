<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAppointment extends Model
{
    public const STATUSES = [
        'scheduled',
        'confirmed',
        'completed',
        'cancelled',
        'no_show',
    ];

    protected $fillable = [
        'doctor_id',
        'patient_name',
        'phone',
        'email',
        'appointment_at',
        'duration_minutes',
        'reason',
        'notes',
        'status',
    ];

    protected $casts = [
        'appointment_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarePartnerRegistrationRequest extends Model
{
    protected $fillable = [
        'plan',
        'practice_type',
        'practice_name',
        'specialty',
        'website_url',
        'address',
        'city',
        'state',
        'postal_code',
        'contact_name',
        'email',
        'phone',
        'message',
        'consented_at',
        'status',
    ];

    protected $casts = [
        'consented_at' => 'datetime',
    ];
}

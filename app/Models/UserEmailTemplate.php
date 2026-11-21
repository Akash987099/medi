<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class UserEmailTemplate extends Model { protected $fillable=['name','subject','body','image_path','is_active']; protected $casts=['is_active'=>'boolean']; public function user(): BelongsTo{return $this->belongsTo(User::class);} }

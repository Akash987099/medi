<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class UserEmailMessage extends Model { protected $fillable=['user_email_template_id','user_patient_id','recipient_email','recipient_name','subject','body','status','failure_reason','sent_at']; protected $casts=['sent_at'=>'datetime']; public function user(): BelongsTo{return $this->belongsTo(User::class);} public function patient(): BelongsTo{return $this->belongsTo(UserPatient::class,'user_patient_id');} public function template(): BelongsTo{return $this->belongsTo(UserEmailTemplate::class,'user_email_template_id');} }

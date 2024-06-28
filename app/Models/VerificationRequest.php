<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationRequest extends Model
{
    use HasFactory;

    protected $table = 'verification_requests';

    protected $fillable = [
        'user_request_id',
        'user_handle_id',
        'verification_card_type',
        'verification_card_image',
        'verification_card_info',
        'reason',
        'status',
        'verification_date',
    ];

    protected $hidden = [
        'user_handle_id',
        'user_request_id',
    ];

    protected $casts = [
        'verification_card_info' => 'array',
        'verification_card_image' => 'array',
        'verification_date' => 'datetime',
    ];

    public function userRequest()
    {
        return $this->belongsTo(User::class, 'user_request_id', 'id');
    }

    public function userHandle()
    {
        return $this->belongsTo(User::class, 'user_handle_id', 'id');
    }
}

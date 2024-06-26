<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WalletTransaction extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'wallet_transactions';

    protected $fillable = [
        'wallet_id',
        'reference_id',
        'transaction_code',
        'transaction_type',
        'transaction_method',
        'status',
        'amount',
        'completed_at',
        'description',
        'bank_info',
        'verification_secret_code'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->verification_secret_code = strtoupper(substr(md5(uniqid()), 0, 10));
        });
    }

    protected $hidden = [
        'wallet_id',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $casts = [
        'bank_info' => 'array',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

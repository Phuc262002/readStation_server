<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'return_date',
        'max_extensions',
        'current_extensions',
        'extension_dates',
        'expired_date',
        'user_id',
        'payment_method',
        'transaction_id',
        'payment_shipping',
        'phone',
        'address',
        'user_note',
        'manager_note',
        'deposit_fee',
        'fine_fee',
        'total_fee',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->order_code = strtoupper('OD'.time().substr(md5(uniqid()), 0, 3));
        });
    }

    protected $hidden = [
        'user_id',
    ];

    protected $casts = [
        'extension_dates' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}

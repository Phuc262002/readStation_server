<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'receipt_date',
        'completed_date',
        'max_extensions',
        'current_extensions',
        'extension_dates',
        'expired_date',
        'user_id',
        'payment_method',
        'transaction_id',
        'phone',
        'address',
        'user_note',
        'manager_note',
        'total_deposit_fee',
        'total_service_fee',
        'total_fine_fee',
        'total_all_fee',
        'shipping_fee',
        'shipping_method_id',
        'return_fee',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->order_code = strtoupper('DH'.intval(substr(strval(microtime(true) * 10000), -6)));
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

    public function transaction()
    {
        return $this->belongsTo(WalletTransaction::class, 'transaction_id');
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }
}

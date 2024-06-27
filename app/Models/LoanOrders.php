<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanOrders extends Model
{
    use HasFactory;

    protected $table = 'loan_orders';

    protected $fillable = [
        'order_code',
        'user_id',
        'payment_method',
        'transaction_id',
        'user_note',
        'max_extensions',
        'current_extensions',
        'loan_date',
        'original_due_date',
        'current_due_date',
        'completed_date',
        'discount',
        'total_deposit_fee',
        'total_service_fee',
        'total_fine_fee',
        'total_shipping_fee',
        'total_return_fee',
        'total_all_fee',
        'delivery_method',
        'delivery_info',
        'shipping_method_id',
        'pickup_date',
        'delivered_date',
        'status',
    ];

    protected $hidden = [
        'user_id',
    ];

    protected $casts = [
        'delivery_info' => 'array',
        'loan_date' => 'datetime',
        'original_due_date' => 'datetime',
        'current_due_date' => 'datetime',
        'completed_date' => 'datetime',
        'pickup_date' => 'datetime',
        'delivered_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->order_code = strtoupper('DH'.intval(substr(strval(microtime(true) * 10000), -6)));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id', 'id');
    }

    public function loanOrderDetails()
    {
        return $this->hasMany(LoanOrderDetails::class, 'loan_order_id', 'id');
    }

    public function extensions()
    {
        return $this->hasMany(Extensions::class, 'loan_order_id', 'id');
    }

    public function transaction()
    {
        return $this->belongsTo(WalletTransaction::class, 'transaction_id', 'id');
    }

    public function extensionDetails()
    {
        return $this->hasManyThrough(ExtensionDetails::class, Extensions::class, 'loan_order_id', 'extension_id', 'id', 'id');
    }

}

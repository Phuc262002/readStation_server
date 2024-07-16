<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnHistory extends Model
{
    use HasFactory;

    protected $table = 'return_histories';

    protected $fillable = [
        'loan_order_details_id',
        'return_date',
        'condition',
        'actual_return_condition',
        'fine_amount',
        'processed_by',
        'return_method',
        'pickup_info',
        'shipping_method_id',
        'return_shipping_fee',
        'pickup_date',
        'received_at_library_date',
        'status',
    ];

    protected $casts = [
        'pickup_info' => 'array',
        'return_date' => 'datetime',
        'pickup_date' => 'datetime',
        'received_at_library_date' => 'datetime',
    ];

    public function loanOrderDetail()
    {
        return $this->belongsTo(LoanOrderDetails::class, 'loan_order_details_id', 'id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by', 'id');
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id', 'id');
    }

    // public function shippingFeeTransaction()
    // {
    //     return $this->belongsTo(Transaction::class, 'return_shipping_fee', 'id');
    // }
}

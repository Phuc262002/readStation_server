<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'transactions';

    protected $fillable = [
        'reference_id',
        'transaction_code',
        'loan_order_id',
        'portal', // payos, vnpay, cash
        'transaction_type',
        'transaction_method',
        'status',
        'amount',
        'completed_at',
        'expired_at',
        'description',
        'extra_info',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $casts = [
        'extra_info' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loanOrder()
    {
        return $this->belongsTo(LoanOrders::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extensions extends Model
{
    use HasFactory;

    protected $table = 'extensions';

    protected $fillable = [
        'loan_order_id',
        'extension_date',
        'extension_fee',
        'approved_by',
        'fee_transaction_id',
        'status',
    ];

    protected $casts = [
        'extension_date' => 'datetime',
        'new_due_date' => 'datetime',
    ];

    public function loanOrder()
    {
        return $this->belongsTo(LoanOrders::class);
    }

    public function extensionDetails()
    {
        return $this->hasMany(ExtensionDetails::class, 'extension_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function feeTransaction()
    {
        return $this->belongsTo(Transaction::class, 'fee_transaction_id', 'id');
    }
}

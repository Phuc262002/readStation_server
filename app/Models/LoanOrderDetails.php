<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanOrderDetails extends Model
{
    use HasFactory;

    protected $table = 'loan_order_details';

    protected $fillable = [
        'loan_order_id',
        'book_details_id',
        'deposit_fee',
        'service_fee',
        'fine_amount',
        'original_due_date',
        'current_due_date',
        'return_date',
        'actual_return_condition',
        'status',
    ];

    protected $casts = [
        'original_due_date' => 'datetime',
        'current_due_date' => 'datetime',
        'return_date' => 'datetime',
    ];

    public function loanOrder()
    {
        return $this->belongsTo(LoanOrders::class);
    }

    public function bookDetails()
    {
        return $this->belongsTo(BookDetail::class);
    }

    public function extensionsDetails()
    {
        return $this->hasMany(ExtensionDetails::class, 'loan_order_detail_id');
    }

    public function returnHistories()
    {
        return $this->hasMany(ReturnHistory::class);
    }

    public function bookReviews()
    {
        return $this->hasMany(BookReviews::class);
    }
}

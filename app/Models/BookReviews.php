<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookReviews extends Model
{
    use HasFactory;

    protected $table = 'book_reviews';

    protected $fillable = [
        'loan_order_detail_id',
        'book_details_id', 
        'user_id',
        'review_text',
        'rating',
        'review_date',
        'expired_date',
        'status',
    ];

    protected $casts = [
        'review_date' => 'datetime',
        'expired_date' => 'datetime',
    ];

    public function loanOrderDetail()
    {
        return $this->belongsTo(LoanOrderDetails::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookDetails()
    {
        return $this->belongsTo(BookDetail::class);
    }


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookReview extends Model
{
    use HasFactory;

    protected $table = 'book_reviews';

    protected $fillable = [
        'loan_order_details_id',
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->status = 'active';
        });
    }

    public function bookDetail()
    {
        return $this->belongsTo(BookDetail::class, 'book_details_id', 'id');
    }

    public function loanOrderDetail()
    {
        return $this->belongsTo(LoanOrderDetails::class, 'loan_order_details_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

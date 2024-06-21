<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_details';

    protected $fillable = [
        'order_id',
        'service_fee',
        'fine_fee',
        'book_details_id',
        'return_date',
        'tomax_extensionstal',
        'current_extensions',
        'extension_dates',
        'expired_date',
        'date_rate',
        'rate',
        'comment',
        'status_cmt',
        'status_od',
        'deposit'
    ];

    protected $hidden = [
        'order_id',
        'book_details_id'
    ];

    protected $casts = [
        'extension_dates' => 'array'
    ];

    public function bookDetail()
    {
        return $this->belongsTo(BookDetail::class, 'book_details_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}

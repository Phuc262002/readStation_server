<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceEnterDetail extends Model
{
    use HasFactory;

    protected $table = 'invoice_enter_details';

    protected $fillable = [
        'invoice_enter_id',
        'book_detail_id',
        'book_price',
        'book_quantity'
    ];

    protected $hidden = [
        'invoice_enter_id',
        'book_detail_id'
    ];

    public function invoiceEnter()
    {
        return $this->belongsTo(InvoiceEnter::class, 'invoice_enter_id');
    }

    public function bookDetail()
    {
        return $this->belongsTo(BookDetail::class, 'book_detail_id');
    }
}

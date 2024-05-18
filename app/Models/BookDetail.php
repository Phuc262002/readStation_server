<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookDetail extends Model
{
    use HasFactory;

    protected $table = 'book_details';

    protected $fillable = [
        'book_id',
        'poster',
        'images',
        'book_version',
        'price',
        'hire_percent',
        'stock',
        'publish_date',
        'publishing_company_id',
        'issuing_company',
        'cardboard',
        'total_page',
        'translator',
        'language',
        'book_size'
    ];

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    // public function publishingCompany()
    // {
    //     return $this->belongsTo(PublishingCompany::class, 'publishing_company_id');
    // }
}

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
        'sku_origin',
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
        'book_size',
        'status',
    ];

    protected $hidden = [
        'book_id',
        'publishing_company_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'images' => 'array',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function order_details()
    {
        return $this->hasMany(OrderDetail::class, 'book_details_id');
    }

    public function publishingCompany()
    {
        return $this->belongsTo(PublishingCompany::class, 'publishing_company_id');
    }

    public function InvoiceEnterDetail()
    {
        return $this->hasMany(InvoiceEnterDetail::class, 'book_detail_id');
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->whereHas('book', function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('original_title', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    public function scopeFilter($query, $category_id, $author_id, $publishing_company_id)
    {
        if ($category_id) {
            $query->whereHas('book', function ($query) use ($category_id) {
                $query->where('category_id', $category_id);
            });
        }

        if ($author_id) {
            $query->whereHas('book', function ($query) use ($author_id) {
                $query->where('author_id', $author_id);
            });
        }

        if ($publishing_company_id) {
            $query->where('publishing_company_id', $publishing_company_id);
        }

        return $query;
    }

    public function delete()
    {
        $this->status = 'deleted';
        $this->save();
    }
}

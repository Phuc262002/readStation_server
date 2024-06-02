<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shelve extends Model
{
    use HasFactory;

    protected $fillable = [
        'bookcase_id',
        'bookshelf_code',
        'category_id',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->bookshelf_code)) {
                $model->bookshelf_code = strtoupper(substr(md5(uniqid()), 0, 10));
            }
        });
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('bookshelf_code', 'like', '%' . $search . '%');
    }

    public function scopeFilter($query, $status, $bookcase_id, $category_id)
    {
        if ($status) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', 'deleted');
        }

        if ($bookcase_id) {
            $query->where('bookcase_id', $bookcase_id);
        }

        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        return $query;
    }

    public function bookcase()
    {
        return $this->belongsTo(Bookcase::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function delete()
    {
        $this->status = 'deleted';
        $this->save();
    }
   
}

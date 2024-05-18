<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'author_id',
        'title',
        'original_title',
        'description_summary',
        'status',
        'description',
        'category_id',
        'shelve_id',
        'slug',
    ];

    protected $hidden = [
        'category_id',
        'author_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->slug = Str::slug($model->title.'-'.Str::random(5));
        });

        static::updating(function ($model) {
            $model->slug = Str::slug($model->title.'-'.Str::random(5));
        });
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function bookDetail()
    {
        return $this->hasMany(BookDetail::class);
    }

    // public function shelve()
    // {
    //     return $this->belongsTo(Shelve::class);
    // }

    public function scopeSearch($query, $search)
    {
        return $query->where('title', 'like', '%' . $search . '%');
    }

    public function scopeFilter($query, $category_id, $status, $author_id)
    {
        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        if ($author_id) {
            $query->where('author_id', $author_id);
        }

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', 'deleted');
        }

        return $query;
    }

    public function delete()
    {
        $this->status = 'deleted';
        $this->save();
    }
}

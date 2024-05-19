<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'author',
        'avatar',
        'dob',
        'is_featured',
        'description',
        'slug',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->slug = Str::slug($model->author . '-' . Str::random(5));
        });

        static::updating(function ($model) {
            $model->slug = Str::slug($model->author . '-' . Str::random(5));
        });
    }

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('author', 'like', '%' . $search . '%');
    }

    public function scopeFilter($query, $type, $status, $is_admin = false)
    {
        if ($status && $is_admin) {
            $query->where('status', $status);
        } else if ($is_admin) {
            $query->where('status', '!=', 'deleted');
        } else {
            $query->where('status', 'active');
        }

        return $query;
    }

    public function delete()
    {
        $this->status = 'deleted';
        $this->save();
    }
}

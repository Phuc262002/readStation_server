<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'content',
        'summary',
        'image',
        'slug',
        'view',
        'status',
    ];

    protected $hidden = [
        'category_id',
        'user_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->slug = Str::slug($model->title . '-' . Str::random(5));
        });

        static::updating(function ($model) {
            $model->slug = Str::slug($model->title . '-' . Str::random(5));
        });
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('title', 'like', '%' . $search . '%');
    }

    public function scopeFilter($query, $category_id, $status, $type)
    {
        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', 'deleted');
        }

        if ($type === 'member') {
            $query->whereHas('user', function ($q) use ($type) {
                $q->where('role_id', 1)->orWhere('role_id', 2);
            });
        } elseif ($type === 'manager') {
            $query->whereHas('user', function ($q) use ($type) {
                $q->where('role_id', 3)->orWhere('role_id', 4);
            });
        }

        return $query;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function delete()
    {
        $this->status = 'deleted';
        $this->save();
    }
}

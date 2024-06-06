<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'description',
        'is_featured',
        'image',
        'slug',
        'type'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->slug = Str::slug($model->name . '-' . Str::random(5));
        });

        static::updating(function ($model) {
            $model->slug = Str::slug($model->name . '-' . Str::random(5));
        });
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%');
    }

    public function scopeFilter($query, $type, $status, $is_admin = false)
    {
        if ($type) {
            $query->where('type', $type);
        }

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
        if ($this->books()->count() > 0 || $this->posts()->count() > 0){
            $this->status = 'deleted';
            $this->save();
        } else {
            parent::delete();
        }

        return true;
    }
}

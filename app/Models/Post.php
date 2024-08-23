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
        'reason_cancel',
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

        if ($status && $status !== 'handle' && $status !== 'client_post') {
            $query->where('status', $status);
        } elseif ($status === 'handle') {
            $query->whereIn('status', ['wating_approve', 'approve_canceled']);
        } elseif ($status === 'client_post') {
            $query->where('status', '!=' , 'deleted');
        } else {
            $query->whereIn('status', ['draft', 'published', 'hidden']);
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

        $comments = $this->comments;
        $comments->each(function ($comment) {
            $comment->delete();
        });
    }
}

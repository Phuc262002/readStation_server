<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'description',
        'type'
    ];

    // public function posts()
    // {
    //     return $this->hasMany(Post::class);
    // }

    // public function books()
    // {
    //     return $this->hasMany(Book::class);
    // }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%');
    }

    public function scopeFilter($query, $type, $status)
    {
        if ($type) {
            $query->where('type', $type);
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

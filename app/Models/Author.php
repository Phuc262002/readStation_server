<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'author',
        'avatar',
        'dob',
        'is_featured',
        'description',
        'status'
    ];

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('author', 'like', '%' . $search . '%');
    }

    public function scopeFilter($query, $type, $status)
    {
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

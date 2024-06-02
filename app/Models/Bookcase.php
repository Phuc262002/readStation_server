<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookcase extends Model
{
    use HasFactory;

    protected $fillable = [
        'bookcase_code',
        'description',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->bookcase_code)) {
                $model->bookcase_code = strtoupper(substr(md5(uniqid()), 0, 10));
            }
        });
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('bookcase_code', 'like', '%' . $search . '%');
    }

    public function scopeFilter($query, $status)
    {
        if ($status) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', 'deleted');
        }

        return $query;
    }

    public function shelves()
    {
        return $this->hasMany(Shelve::class);
    }

    public function books()
    {
        return $this->hasManyThrough(Book::class, Shelve::class);
    }

    public function delete()
    {
        $this->status = 'deleted';
        $this->save();
    }
}

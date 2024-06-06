<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublishingCompany extends Model
{
    use HasFactory;

    protected $table = 'publishing_companies';

    protected $fillable = [
        'name',
        'logo_company',
        'description',
        'status',
    ];

    public function book_details()
    {
        return $this->hasMany(BookDetail::class, 'publishing_company_id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%');
    }

    public function scopeFilter($query, $status, $is_admin = false)
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
        if ($this->book_details()->count() > 0) {
            $this->status = 'deleted';
            $this->save();
        } else {
            parent::delete();
        }

        return true;
    }
}

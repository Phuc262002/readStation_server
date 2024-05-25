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

    public function delete()
    {
        $this->status = 'deleted';
        $this->save();
    }
}

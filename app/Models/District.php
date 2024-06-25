<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'DistrictName',
        'ProvinceID',
        'NameExtension'
    ];

    protected $casts = [
        'NameExtension' => 'array',
    ];

    public function province()
    {
        return $this->belongsTo(Province::class, 'ProvinceID');
    }
}

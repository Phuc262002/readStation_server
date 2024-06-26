<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'WardName',
        'DistrictID',
        'NameExtension'
    ];

    protected $casts = [
        'id' => 'string',
        'NameExtension' => 'array',
    ];

    public function district()
    {
        return $this->belongsTo(District::class, 'DistrictID');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $table = 'shipping_methods';

    protected $fillable = [
        'method',
        'fee',
        'logo',
        'note',
        'location',
        'status'
    ];

    protected $casts = [
        'location' => 'array'
    ];

    public function scopeSearch($query, $search)
    {
        return $query->where('method', 'like', '%' . $search . '%');
    }

    public function scopeFilter($query, $status)
    {
        if ($status) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function order()
    {
        return $this->hasMany(LoanOrders::class);
    }

    public function delete()
    {
        if ($this->order()->count() > 0) {
            $this->status = 'deleted';
            $this->save();
        } else {
            parent::delete();
        }

        return true;
    }
}

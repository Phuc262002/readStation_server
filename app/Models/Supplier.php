<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'status',
    ];

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%');
    }

    public function scopeFilter($query, $status,)
    {
        if ($status) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', 'deleted');
        }

        return $query;
    }

    public function invoice_enters()
    {
        return $this->hasMany(InvoiceEnter::class, 'supplier_id');
    }

    public function delete()
    {
        if ($this->invoice_enters()->count() > 0){
            $this->status = 'deleted';
            $this->save();
        } else {
            parent::delete();
        }

        return true;
    }
}

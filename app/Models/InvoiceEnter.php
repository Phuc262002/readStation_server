<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceEnter extends Model
{
    use HasFactory;

    protected $table = 'invoice_enters';

    protected $fillable = [
        'user_id',
        'invoice_code',
        'invoice_name',
        'total',
        'invoice_description',
        'supplier_id',
        'invoice_date',
        'status'
    ];

    protected $hidden = [
        'supplier_id',
        'user_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->invoice_code)) {
                $model->invoice_code = strtoupper(substr(md5(uniqid()), 0, 10));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function invoiceEnterDetails()
    {
        return $this->hasMany(InvoiceEnterDetail::class, 'invoice_enter_id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('invoice_code', 'like', '%' . $search . '%')->orWhere('invoice_name', 'like', '%' . $search . '%');
    }

    public function scopeFilter($query, $status, $supplier_id)
    {
        if ($status) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', 'deleted');
        }

        if ($supplier_id) {
            $query->where('supplier_id', $supplier_id);
        }

        return $query;
    }
}

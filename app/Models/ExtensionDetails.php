<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtensionDetails extends Model
{
    use HasFactory;

    protected $table = 'extension_details';

    protected $fillable = [
        'extension_id',
        'loan_order_detail_id',
        'new_due_date',
        'extension_fee',
    ];

    protected $casts = [
        'new_due_date' => 'datetime',
    ];

    public function extensionDetails()
    {
        return $this->belongsTo(Extensions::class, 'extension_id');
    }

    public function loanOrderDetail()
    {
        return $this->belongsTo(LoanOrderDetails::class, 'loan_order_detail_id', 'id');
    }
}

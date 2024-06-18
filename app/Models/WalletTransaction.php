<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WalletTransaction extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'wallet_transactions';

    protected $fillable = [
        'wallet_id',
        'reference_id',
        'transaction_code',
        'transaction_type',
        'transaction_method',
        'status',
        'amount',
        'completed_at',
        'description'
    ];

    protected $hidden = [
        'wallet_id',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

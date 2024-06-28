<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WalletHistory extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'wallet_history';

    protected $fillable = [
        'wallet_id',
        'previous_balance',
        'new_balance',
        'previous_status',
        'new_status',
        'action',
        'reason',
    ];
}

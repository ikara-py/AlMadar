<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'account_id',
        'transfer_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function account(){
        return $this->belongsTo(Account::class);
    }

    public function transfer(){
        return $this->belongsTo(Transfer::class);
    }

}

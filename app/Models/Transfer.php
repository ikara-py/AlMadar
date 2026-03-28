<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'from_account_id',
        'to_account_id',
        'amount',
        'description',
        'status',
        'initiated_by',
        'failure_reason'
    ];
    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function fromAccount(){
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount(){
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function initiatedBy(){
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }

}

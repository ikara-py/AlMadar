<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'account_number',
        'type',
        'status',
        'balance',
        'overdraft_limit',
        'interest_rate',
        'monthly_fee',
        'blocked_reason',
        'guardian_id'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'overdraft_limit' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
    ];

    public function isActive()
    {
        return $this->status === "ACTIVE";
    }

    public function isBlocked()
    {
        return $this->status == 'BLOCKED';
    }

    public function isClosed()
    {
        return $this->status === 'CLOSED';
    }


    public function isCourant()
    {
        return $this->type === 'COURANT';
    }
    public function isEpargne()
    {
        return $this->type === 'EPARGNE';
    }
    public function isMineur()
    {
        return $this->type === 'MINEUR';
    }

    public function getEffectiveOverdraftLimit()
    {
        if ($this->isCourant()) {
            return (float) ($this->overdraft_limit ?? config('banking.overdraft_limit'));
        }
        return 0.0;
    }


    public function canBeDebeted(float $amount)
    {
        return ($this->balance - $amount) >= -$this->getEffectiveOverdraftLimit();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'account_user')
            ->withPivot('role', 'accepted_closure')->withTimestamps();
    }

    public function guardian()
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function outgouingTransfers()
    {
        return $this->hasMany(Transfer::class, 'from_account_id');
    }

    public function incomingTransfers()
    {
        return $this->hasMany(Transfer::class, 'to_account_id');
    }
}

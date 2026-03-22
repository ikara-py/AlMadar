<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\Transaction;

class TransactionRepository
{
    public function create(array $data)
    {
        return Transaction::create($data);
    }

    public function getForAccount(Account $account, array $filters = [])
    {
        $q = $account->transactions()->with('Transfer');

        if (!empty($filters['date_from'])) {
            $q->whereDate('created_at', '>=', $filters['date_from']);
        }


        if (!empty($filters['date_to'])) {
            $q->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $q->orderBy('created_at', 'desc')->paginate(20);
    }
}

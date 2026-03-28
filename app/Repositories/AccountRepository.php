<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AccountRepository
{
    public function find(int $id)
    {
        return Account::with(['users', 'guardian'])->find($id);
    }

    public function findOrFail(int $id)
    {
        return Account::with(['users', 'guardian'])->findOrFail($id);
    }

    public function getForUser(User $user)
    {
        return $user->accounts()->with('users', 'guardian')->get();
    }

    public function getAll()
    {
        return Account::with('users', 'guardian')->get();
    }

    public function create(array $data)
    {
        return Account::create($data);
    }

    public function update(Account $account, array $data)
    {
        $account->update($data);
        return $account->fresh();
    }

    public function attachUser(Account $account, $userId, $role)
    {
        $account->users()->attach($userId, ['role' => $role]);
    }

    public function detachUser(Account $account, $userId)
    {
        $account->users()->detach($userId);
    }

    public function updatePivot(Account $account, $userId, $data)
    {
        $account->users()->updateExistingPivot($userId, $data);
    }

    public function countMonthlyWithdrawals(Account $account): int
    {
        return $account->transactions()
            ->where('type', 'DEBIT')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function getDailyTransferTotal(Account $account): float
    {
        return (float) $account->transactions()
            ->where('type', 'DEBIT')
            ->whereDate('created_at', today())
            ->sum('amount');
    }
}

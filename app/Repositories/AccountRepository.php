<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AccountRepository{
    public function find(int $id){
        return Account::with(['users', 'guardian'])->find($id);
    }

    public function findOrfail(int $id){
        return Account::with(['users', 'guardian'])->findOrFail($id);
    }

    public function getForUser(User $user){
        return $user->accounts()->with('users', 'guardian')->get();
    }
}
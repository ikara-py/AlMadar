<?php
namespace App\Services;

use App\Models\Account;
use App\Repositories\AccountRepository;
use Illuminate\Validation\ValidationException;

class AdminService
{
    public function __construct(private AccountRepository $accountRepository) {}

    public function blockAccount(Account $account, string $reason): Account
    {
        if (!$account->isActive())
            throw ValidationException::withMessages(['account' => ['Only ACTIVE accounts can be blocked.']]);
        return $this->accountRepository->update($account, ['status' => 'BLOCKED', 'blocked_reason' => $reason]);
    }

    public function unblockAccount(Account $account): Account
    {
        if (!$account->isBlocked())
            throw ValidationException::withMessages(['account' => ['Only BLOCKED accounts can be unblocked.']]);
        return $this->accountRepository->update($account, ['status' => 'ACTIVE', 'blocked_reason' => null]);
    }

    public function closeAccount(Account $account): Account
    {
        $allAccepted = $account->users->every(fn ($u) => $u->pivot->accepted_closure);
        if (!$allAccepted)
            throw ValidationException::withMessages(['account' => ['Not all holders have consented to closure.']]);

        if ((float) $account->balance !== 0.0)
            throw ValidationException::withMessages(['balance' => ['Balance must be 0.00 before closing.']]);

        return $this->accountRepository->update($account, ['status' => 'CLOSED']);
    }
}

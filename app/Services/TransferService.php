<?php

namespace App\Services;

use App\Models\Account;
use App\Models\User;
use App\Models\Transfer;
use App\Repositories\AccountRepository;
use App\Repositories\TransferRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferService
{
    public function __construct(
        private AccountRepository     $accountRepository,
        private TransferRepository    $transferRepository,
        private TransactionRepository $transactionRepository,
    ) {}

    public function initiate(User $initiator, array $data): Transfer
    {
        $fromAccount = $this->accountRepository->findOrFail($data['from_account_id']);
        $toAccount   = $this->accountRepository->findOrFail($data['to_account_id']);
        $amount      = (float) $data['amount'];

        $this->validateTransfer($initiator, $fromAccount, $toAccount, $amount);

        $transfer = $this->transferRepository->create([
            'from_account_id' => $fromAccount->id,
            'to_account_id'   => $toAccount->id,
            'amount'          => $amount,
            'description'     => $data['description'] ?? null,
            'status'          => 'PENDING',
            'initiated_by'    => $initiator->id,
        ]);

        try {
            DB::transaction(function () use ($transfer, $fromAccount, $toAccount, $amount) {
                $debitBefore = (float) $fromAccount->balance;
                $debitAfter  = $debitBefore - $amount;
                $fromAccount->update(['balance' => $debitAfter]);
                $this->transactionRepository->create([
                    'account_id'     => $fromAccount->id,
                    'transfer_id' => $transfer->id,
                    'type'           => 'DEBIT',
                    'amount' => $amount,
                    'balance_before' => $debitBefore,
                    'balance_after' => $debitAfter,
                    'description'    => 'Transfer to ' . $toAccount->account_number,
                    'status'         => 'COMPLETED',
                ]);

                $creditBefore = (float) $toAccount->balance;
                $creditAfter  = $creditBefore + $amount;
                $toAccount->update(['balance' => $creditAfter]);
                $this->transactionRepository->create([
                    'account_id'     => $toAccount->id,
                    'transfer_id' => $transfer->id,
                    'type'           => 'CREDIT',
                    'amount' => $amount,
                    'balance_before' => $creditBefore,
                    'balance_after' => $creditAfter,
                    'description'    => 'Transfer from ' . $fromAccount->account_number,
                    'status'         => 'COMPLETED',
                ]);

                $transfer->update(['status' => 'COMPLETED']);
            });
        } catch (\Exception $e) {
            $transfer->update(['status' => 'FAILED', 'failure_reason' => $e->getMessage()]);
        }

        return $transfer->fresh()->load('fromAccount', 'toAccount', 'transactions');
    }

    private function validateTransfer(User $initiator, Account $from, Account $to, float $amount): void
    {
        if ($from->id === $to->id)
            throw ValidationException::withMessages(['to_account_id' => ['Cannot transfer to the same account.']]);

        if (!$from->isActive())
            throw ValidationException::withMessages(['from_account_id' => ['Source account is not active.']]);

        if (!$to->isActive())
            throw ValidationException::withMessages(['to_account_id' => ['Destination account is not active.']]);

        $this->validateInitiatorAuthorization($initiator, $from);

        if (!$from->canBeDebited($amount))
            throw ValidationException::withMessages(['amount' => ['Insufficient balance.']]);

        if ($from->isEpargne()) {
            $count = $this->accountRepository->countMonthlyWithdrawals($from);
            $max   = config('banking.max_savings_withdrawals');
            if ($count >= $max)
                throw ValidationException::withMessages(['from_account_id' => ["EPARGNE max {$max} withdrawals/month reached."]]);
        }

        if ($from->isMineur()) {
            $count = $this->accountRepository->countMonthlyWithdrawals($from);
            $max   = config('banking.max_minor_withdrawals');
            if ($count >= $max)
                throw ValidationException::withMessages(['from_account_id' => ["MINEUR max {$max} withdrawals/month reached."]]);
        }

        $dailyTotal = $this->accountRepository->getDailyTransferTotal($from);
        $dailyLimit = config('banking.daily_transfer_limit');
        if (($dailyTotal + $amount) > $dailyLimit)
            throw ValidationException::withMessages(['amount' => ["Daily transfer limit of {$dailyLimit} MAD exceeded."]]);
    }

    private function validateInitiatorAuthorization(User $initiator, Account $from): void
    {
        if ($from->isMineur()) {
            if ($from->guardian_id !== $initiator->id)
                throw ValidationException::withMessages(['from_account_id' => ['Only the guardian can initiate transfers from a MINEUR account.']]);
            return;
        }
        if (!$from->users->contains($initiator->id))
            throw ValidationException::withMessages(['from_account_id' => ['You are not a holder of this account.']]);
    }
}

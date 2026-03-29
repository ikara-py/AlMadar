<?php
namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ScheduledTaskService
{
    public function chargeMonthlyFees(): void
    {
        Account::where('type', 'COURANT')
            ->where('status', 'ACTIVE')
            ->lazy()
            ->each(function ($account) {
                $fee = (float) ($account->monthly_fee ?? config('banking.monthly_fee'));

                DB::transaction(function () use ($account, $fee) {
                    $before = (float) $account->balance;

                    if ($before >= $fee) {
                        $after = $before - $fee;
                        $account->update(['balance' => $after]);
                        Transaction::create([
                            'account_id' => $account->id,
                            'type' => 'FEE',
                            'amount' => $fee,
                            'status' => 'COMPLETED',
                            'balance_before' => $before,
                            'balance_after' => $after,
                            'description' => 'Monthly account maintenance fee',
                        ]);
                    } else {
                        $account->update([
                            'status' => 'BLOCKED',
                            'blocked_reason' => 'Insufficient balance to cover monthly fee'
                        ]);
                        Transaction::create([
                            'account_id' => $account->id,
                            'type' => 'FEE_FAILED',
                            'amount' => $fee,
                            'status' => 'FAILED',
                            'balance_before' => $before,
                            'balance_after' => $before,
                            'description' => 'Monthly fee failed — account blocked',
                        ]);
                    }
                });
            });
    }

    public function applyMonthlyInterest(): void
    {
        Account::whereIn('type', ['EPARGNE', 'MINEUR'])
            ->where('status', 'ACTIVE')
            ->whereNotNull('interest_rate')
            ->where('interest_rate', '>', 0)
            ->lazy()
            ->each(function ($account) {
                DB::transaction(function () use ($account) {
                    $annualRate  = (float) $account->interest_rate;
                    $monthlyRate = $annualRate / 12 / 100;
                    $before      = (float) $account->balance;
                    $interest    = round($before * $monthlyRate, 2);

                    if ($interest <= 0) return;

                    $after = $before + $interest;
                    $account->update(['balance' => $after]);
                    Transaction::create([
                        'account_id' => $account->id,
                        'type' => 'INTEREST',
                        'amount' => $interest,
                        'status' => 'COMPLETED',
                        'balance_before' => $before,
                        'balance_after' => $after,
                        'description' => "Monthly interest at {$annualRate}% annual rate",
                    ]);
                });
            });
    }
}

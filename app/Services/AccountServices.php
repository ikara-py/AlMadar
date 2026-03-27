<?php

namespace App\Services;

use App\Models\Account;
use App\Models\User;
use App\Repositories\AccountRepository;
use App\Repositories\UserRepository;
use Illuminate\Validation\ValidationException;

class AccountServices{
    public function __construct(private AccountRepository $accountRepository, private UserRepository $userRepository){}

    private function generateAccountNumber(){
        do {
            $number = 'MA' .str_pad(random_int(1, 9999999), 7, '0', STR_PAD_LEFT);
        }while(Account::where('account_number', $number)->exists());
        return $number;
    }

    public function create(User $creator, $data){
        if($data['type'] === 'MINEUR'){
            if(empty($data['guardian_id'])){
                throw ValidationException::withMessages(['guardian_id' => ['A guardian is required to create this account']]);   
            }

            $guardian = $this->userRepository->findOrFail($data['guardian_id']);

            if (!$creator->isMinor()){
                throw ValidationException::withMessages(['type' => ['the creator can not be a minor']]);
            }
        }

        $accountData = [
            'account_number' => $this->generateAccountNumber(),
            'type' => $data['type'],
            'status' => 'ACTIVE',
            'balance' => 0.00,
            'guardian_id' => $data['guardian_id'] ?? null,
        ];

        match ($data['type']){
            'COURANT' => $accountData +=[
                'overdraft_limit' => $data['overdraft_limit'] ?? config('banking.overdraft_limit'),
                'monthly_fee' => $data['monthly_fee'] ?? config('banking.monthly_fee'),
            ],
            'EPARGNE' => $accountData +=[
                'intrest_rate' => $data['interest_rate'] ?? config('banking.savings_interest_rate'),
            ],
            'MINEUR' => $accountData +=[
                'intrest_rate' => $data['interest_rate'] ?? config('banking.minor_interest_rate'),
            ],
            default => throw new \Exception("Invalid account type"),
        };

        $account = $this->accountRepository->create($accountData);
        $this->accountRepository->attachUser($account, $creator->id, 'PROPRIETAIRE');

        return $account;
    }
}
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
                'interest_rate' => $data['interest_rate'] ?? config('banking.savings_interest_rate'),
            ],
            'MINEUR' => $accountData +=[
                'interest_rate' => $data['interest_rate'] ?? config('banking.minor_interest_rate'),
            ],
            default => throw new \Exception("Invalid account type"),
        };

        $account = $this->accountRepository->create($accountData);
        $this->accountRepository->attachUser($account, $creator->id, 'owner');
        return $account->load('users', 'guardian');
    }

    public function requestClosure(Account $account, User $requester){
        if(!$account->isActive()){
            throw ValidationException::withMessages(['account' => ['only active accounts can be closed']]);
        }

        if((float) $account->balance !== 0.0){
            throw ValidationException::withMessages(['balance' => ['Balance must be 0 before closing it']]);
        }

        $this->accountRepository->updatePivot($account, $requester->id, ['accepted_closure' => true]);

        $account->refresh();

        $allAccepted = $account->users->every(fn ($u) => $u->pivot->accepted_closure);
        return [
            'message' => $allAccepted ? 'Account is ready for admin closure.' : 'request has been recorded.',
            'all_accepted' => $allAccepted,
        ];
    }

    public function convertMinorToCurrentAccount(Account $account, User $initiator){
        if(!$account->isMineur()){
            throw ValidationException::withMessages(['account' => ['only Mineur can be covered']]);
        }

        if($account->guardian_id !== $initiator->id){
            throw ValidationException::withMessages(['account' => ['Only the guardian can convert a MINEUR account.']]);
        }

        $minorHolder = $account->users->first(fn ($u) => $u->id !== $account->guardian_id);

        if($minorHolder && $minorHolder->isMinor()){
            throw ValidationException::withMessages(['account' => ['The account holder is still a minor.']]);
        }

        return $this->accountRepository->update($account, [
            'type' => 'COURANT',
            'guardian_id' => null,
            'overdraft_limit' => config('banking.overdraft_limit'),
            'monthly_fee' => config('banking.monthly_fee'),
            'interest_rate' => null,
        ]);
    }

    public function addCoOwner(Account $account, $userId){
        $user = $this->userRepository->findOrFail($userId);
        if($account->users->contains($user->id)){
            throw ValidationException::withMessages(['user_id' => ['This user is already a holder of this account.']]);
        }

        $this->accountRepository->attachUser($account, $user->id, 'co_owner');        
        }
            
    public function removeCoOwner(Account $account, $userId){
            $this->accountRepository->detachUser($account, $userId);
        }
}
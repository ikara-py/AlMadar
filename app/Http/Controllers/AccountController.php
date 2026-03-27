<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\CreateAccountRequest;
use App\Repositories\AccountRepository;
use App\Repositories\TransactionRepository;
use App\Services\AccountServices;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(private AccountServices $accountServices,
                                private AccountRepository $accountRepository,
                                private TransactionRepository $transactionRepository){}
    public function index(Request $request){
        return response()->json(
            $this->accountRepository->getForUser($request->user())
        );
    }

    public function store(CreateAccountRequest $request){
        $account = $this->accountServices->create($request->user(), $request->validated());
        return response()->json([
            'message' => 'stored successfully',
            $account
        ],201);
    }

    public function show($id){
        $account = $this->accountRepository->findOrfail($id);
        $this->authorizeAccountAccess($account);
        return response()->json($account);    
    }


    public function addCoOwner(Request $request, $id){
        $account = $this->accountRepository->findOrFail($id);
        $this->authorizeAccountAccess($account);
        $this->accountServices->addCoOwner($account, $request->input('user_id'));
        return response()->json(['message' => 'Co-owner added successfully.']);

    }

    public function removeCoOwner($id, $userId){
        $account = $this->accountRepository->findOrfail($id);
        $this->authorizeAccountAccess($account);
        $this->accountServices->removeCoOwner($account, $userId);
        return response()->json(['message' => 'Co-Owner removed']);
    }

    
    public function convert($id){
        $account = $this->accountRepository->findOrfail($id);
        $upgrade = $this->accountServices->convertMinorToCurrentAccount($account, auth()->user());
    }

    public function requestClosure($id){
        $account = $this->accountRepository->findOrfail($id);
        $this->authorizeAccountAccess($account);
        return response()->json( $this->accountServices->requestClosure($account, auth()->user()));
    }

    public function transactions(Request $request, $id){
        $account = $this->accountRepository->findOrfail($id);
        $this->authorizeAccountAccess($account);
        return response()->json($this->transactionRepository->getForAccount($account, $request->only(['type', 'date_from', 'date_to'])));
    }

        private function authorizeAccountAccess($account)
    {
        $user = auth()->user();
        if (
            !$account->users->contains($user->id) &&
            $account->guardian_id !== $user->id &&
            !$user->isAdmin()
        ) {
            abort(403, 'You are not authorized to access this account.');
        }
    }

}

<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlockAccountRequest;
use App\Repositories\AccountRepository;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function __construct(
        private AdminService      $adminService,
        private AccountRepository $accountRepository,
    ) {}

    public function allAccounts(): JsonResponse
    {
        return response()->json($this->accountRepository->getAll());
    }

    public function block(BlockAccountRequest $request, int $id): JsonResponse
    {
        $account = $this->accountRepository->findOrFail($id);
        return response()->json($this->adminService->blockAccount($account, $request->validated('reason')));
    }

    public function unblock(int $id): JsonResponse
    {
        $account = $this->accountRepository->findOrFail($id);
        return response()->json($this->adminService->unblockAccount($account));
    }

    public function close(int $id): JsonResponse
    {
        $account = $this->accountRepository->findOrFail($id);
        return response()->json($this->adminService->closeAccount($account));
    }
}

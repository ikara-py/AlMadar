<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transfer\CreateTransferRequest;
use App\Repositories\TransferRepository;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function __construct(
        private TransferService    $transferService,
        private TransferRepository $transferRepository,
    ) {}

    public function store(CreateTransferRequest $request): JsonResponse
    {
        $user = $request->user();
        
        $transfer = $this->transferService->initiate($user, $request->validated());
        
        return response()->json($transfer, 201);
    }

    public function show(Request $request, int $id): JsonResponse 
    {
        $transfer = $this->transferRepository->findOrFail($id);
        
        $user = $request->user();
        
        $canSee = $transfer->fromAccount->users->contains($user->id) 
               || $transfer->toAccount->users->contains($user->id)
               || $user->isAdmin();
               
        if (!$canSee) {
            abort(403);
        }
        
        return response()->json($transfer);
    }
}

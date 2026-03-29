<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(private UserRepository $userRepository) {}

    public function me(Request $request): JsonResponse 
    { 
        return response()->json($request->user()); 
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name'  => 'sometimes|string|max:100',
            'email'      => 'sometimes|email|unique:users,email,' . $user->id,
        ]);
        return response()->json($this->userRepository->update($user->id, $data));
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);
        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password))
            throw ValidationException::withMessages(['current_password' => ['The current password is incorrect.']]);

        $this->userRepository->update($user->id, ['password' => Hash::make($request->password)]);
        return response()->json(['message' => 'Password updated successfully.']);
    }
}

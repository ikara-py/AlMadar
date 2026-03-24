<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct( private AuthService $authService){}

    public function register(RegisterRequest $request){
        $user = $this->authService->register($request->validated());
        return response()->json(['message' => 'User registered successfully.',], 201);
    }

    public function login(LoginRequest $request){
        $result = $this->authService->login($request->validated());
        if (!$result){
            return response()->json([
                'message' => 'Invalid'
            ], 401);
        }
        return response()->json($result);
    }

    public function refresh(){
        try {
            $token = $this->authService->refresh();
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'token can not be refreshed',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    public function logout(){
        $this->authService->logout();
        return response()->json([
            'message' => 'logged out successfully'
        ]);
    }
}

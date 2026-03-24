<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use App\Repositories\UserRepository;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{

    public function __construct(private UserRepository $userRepository) {}
    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return $this->userRepository->create($data);
    }

    public function login(array $credentials)
    {
        $token = JWTAuth::attempt([
            'email'    => $credentials['email'],
            'password' => $credentials['password'],
        ]);

        if (!$token) return null;

        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => config('jwt.ttl') * 60,
            'user'         => JWTAuth::user(),
        ];
    }

    public function refresh(): string
    {
        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        return $guard->refresh();
    }

    public function logout(): void
    {
        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        $guard->logout();
    }
}

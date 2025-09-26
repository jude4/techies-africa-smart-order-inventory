<?php

namespace App\Http\Controllers\User;

use App\Helpers\ResponseHelper;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        if (!Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            return ResponseHelper::error('The provided credentials are incorrect.', [], 404);
        }

        [$user, $authToken] = $this->userService->login($validated);

        return $authToken
            ? ResponseHelper::success(['user' => $user, 'auth_token' => $authToken], 'User login successfully')
            : ResponseHelper::error('There was an error creating auth token, please try again', [], 500);
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        [$user, $authToken] = $this->userService->register($validated);

        return $authToken
            ? ResponseHelper::success(['user' => $user->refresh(), 'auth_token' => $authToken], 'User registered successfully')
            : ResponseHelper::error('Registration was successful but there was an error creating auth token, please login', [], 500);
    }
}

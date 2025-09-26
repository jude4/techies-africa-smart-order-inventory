<?php

namespace App\Services;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function login(array $data): array
    {
        // get user details
        $user = User::whereEmail($data['email'])->first();

        if (!$user) {
            // authentication should have failed earlier, but guard against it
            ResponseHelper::error('User not found.', [], 404);
        }

        // create auth token (plain text)
        $authToken = $this->createUserAuthToken($user, 'authToken');

        // check if token was created
        if (!$authToken) {
            ResponseHelper::error('There was a server error creating auth token, try again.', [], 500);
        }

        return [$user, $authToken];
    }

    public function register(array $data): array
    {
        // Hash password
        $data['password'] = Hash::make($data['password']);

        // create user
        $user = User::create($data);

        // if user was not created return error
        if (!$user) {
            ResponseHelper::error('There was an error while creating user', [], 500);
        }

        // create sanctum authentication token (plain text)
        $authToken = $this->createUserAuthToken($user, 'authToken');

        return [$user, $authToken];
    }

    private function createUserAuthToken(User $user, string $tokenName)
    {
        // createToken returns a NewAccessToken object; return the plain-text token
        $token = $user->createToken($tokenName);
        return $token->plainTextToken;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('email', $data['email'])->first();
        if (!$admin || !Hash::check($data['password'], $admin->password)) {
            return ResponseHelper::error('Invalid admin credentials', [], 401);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;
        return ResponseHelper::success(['admin' => $admin, 'auth_token' => $token], 'Admin logged in');
    }
}

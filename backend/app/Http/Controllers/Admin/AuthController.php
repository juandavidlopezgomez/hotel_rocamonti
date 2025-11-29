<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->is_admin) {
                $token = $user->createToken('admin-token')->plainTextToken;
                return response()->json(['token' => $token]);
            }
        }

        return response()->json(['message' => 'Invalid credentials or not an admin.'], 401);
    }
}

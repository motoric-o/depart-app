<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // POST /api/register
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:accounts',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        // 1. Get Customer Type ID (Assuming 'Customer' exists from Seeder)
        $customerType = AccountType::where('name', 'Customer')->first();
        
        if (!$customerType) {
            return response()->json(['error' => 'System configuration error: Customer type missing'], 500);
        }

        // 2. Create Account via SP (Procedure)
        \Illuminate\Support\Facades\DB::statement("CALL sp_register_user(?, ?, ?, ?, NULL, ?)", [
            $request->first_name,
            $request->last_name,
            $request->email,
            $request->phone,
            Illuminate\Support\Facades\Hash::make($request->password)
        ]);

        // 3. Refresh to get the DB-generated ID (Implicitly needed since SP procedure doesn't return ID directly in simple CALL without INOUT, 
        // but wait, sp_register_user definition doesn't use INOUT for ID. So we fetch by email.)
        $account = Account::where('email', $request->email)->first();

        // 4. Create Token
        $token = $account->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $account
        ], 201);
    }

    // POST /api/login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 1. Find User using Stored Function
        // sp_login_user returns a table, but we use DB::select
        $results = \Illuminate\Support\Facades\DB::select("SELECT * FROM sp_login_user(?)", [$request->email]);
        
        if (empty($results)) {
             throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        
        $userRow = $results[0]; // Raw object

        // 2. Check Password
        if (! Hash::check($request->password, $userRow->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        
        // 3. Generate Token (Need Model for this)
        $account = Account::find($userRow->id);

        // Deletes old tokens to force single-session (Optional safety)
        $account->tokens()->delete(); 
        
        $token = $account->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $account,
            'account_type' => $account->accountType
        ]);
    }

    // GET /api/user (Protected Route)
    public function me(Request $request)
    {
        return $request->user();
    }

    // POST /api/logout (Protected Route)
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
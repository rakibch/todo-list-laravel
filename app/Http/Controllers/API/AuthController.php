<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Authentication
 * 
 * APIs for user registration, login, and logout using Laravel Sanctum.
 */
class AuthController extends Controller
{
     /**
     * Register a new user.
     *
     * @bodyParam name string required The name of the user. Example: John Doe
     * @bodyParam email string required The email of the user. Example: john@example.com
     * @bodyParam password string required The password (minimum 6 characters). Example: secret123
     * @bodyParam password_confirmation string required Must match the password. Example: secret123
     *
     * @response 201 {
     *   "token": "1|bVhmL4t...etc"
     * }
     * 
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": ["The email has already been taken."]
     *   }
     * }
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json(['token' => $token], 201);
    }

    /**
     * Login an existing user.
     *
     * @bodyParam email string required The email address. Example: john@example.com
     * @bodyParam password string required The user's password. Example: secret123
     * 
     * @response 200 {
     *   "token": "1|bVhmL4t...etc"
     * }
     * 
     * @response 422 {
     *   "message": "The credentials are incorrect.",
     *   "errors": {
     *     "email": ["The credentials are incorrect."]
     *   }
     * }
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    /**
     * Logout the authenticated user.
     *
     * Requires a valid Bearer token.
     * 
     * @authenticated
     * 
     * @response 200 {
     *   "message": "Logged out"
     * }
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}


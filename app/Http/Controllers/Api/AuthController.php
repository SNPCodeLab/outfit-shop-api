<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user and issue a Sanctum token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'is_admin' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $request->boolean('is_admin', false),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success'      => true,
            'message'      => 'User registered successfully.',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'is_admin' => (bool) $user->is_admin,
            ],
        ], 201);
    }

    /**
     * Authenticate user/employee credentials and issue a Sanctum token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        // Attempt User authentication
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success'      => true,
                'message'      => 'Login successful.',
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'email'    => $user->email,
                    'is_admin' => (bool) $user->is_admin,
                ],
            ]);
        }

        // Fallback to Employee authentication
        $employee = \App\Models\Employee::where('username', $request->email)
            ->orWhere('email', $request->email)
            ->first();

        if ($employee && Hash::check($request->password, $employee->password_hash)) {
            $token = $employee->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success'      => true,
                'message'      => 'Employee login successful.',
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => [
                    'id'       => $employee->employee_id,
                    'name'     => $employee->employee_name,
                    'email'    => $employee->email,
                    'position' => $employee->position,
                    'is_admin' => strtoupper($employee->role ?? '') === 'ADMIN',
                ],
            ]);
        }

        throw ValidationException::withMessages([
            'email' => ['Invalid login credentials provided.'],
        ]);
    }

    /**
     * Get the authenticated user profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user'    => $request->user(),
        ]);
    }

    /**
     * Log out the authenticated user (revoke current token).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}

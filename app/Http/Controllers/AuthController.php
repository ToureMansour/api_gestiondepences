<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\LoggingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected AuthService $authService;
    protected LoggingService $loggingService;

    public function __construct(AuthService $authService, LoggingService $loggingService)
    {
        $this->authService = $authService;
        $this->loggingService = $loggingService;
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'sometimes|in:admin,employee',
        ]);

        if ($validator->fails()) {
            $this->loggingService->logValidationError('/api/register', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->authService->register($request->all());
            $this->loggingService->logAction('register', 'user', $result['user']->reference, $result['user']->id);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $result
            ], 201);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, 'AuthController@register');
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $this->loggingService->logValidationError('/api/login', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->authService->login($request->only('email', 'password'));
            $this->loggingService->logAuthAttempt($request->email, true, request()->ip());
            $this->loggingService->logAction('login', 'user', $result['user']->reference, $result['user']->id);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => $result
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->loggingService->logAuthAttempt($request->email, false, request()->ip());
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, 'AuthController@login');
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $userReference = auth()->user()->reference;
            $userId = auth()->id();
            $this->authService->logout();
            $this->loggingService->logAction('logout', 'user', $userReference, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ]);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, 'AuthController@logout');
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Interfaces\UserRepositoryInterface;
use App\Services\LoggingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected UserRepositoryInterface $userRepository;
    protected LoggingService $loggingService;

    public function __construct(UserRepositoryInterface $userRepository, LoggingService $loggingService)
    {
        $this->userRepository = $userRepository;
        $this->loggingService = $loggingService;
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin')->except(['profile', 'updateProfile']);
    }

    public function index(): JsonResponse
    {
        try {
            $users = $this->userRepository->getAllPaginated();

            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $userReference): JsonResponse
    {
        try {
            $user = $this->userRepository->findByReference($userReference);

            if (!$user) {
                $this->loggingService->logWarning('User not found', ['reference' => $userReference]);
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $this->loggingService->logAction('view', 'user', $userReference, $user->id);
            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, 'UserController@show');
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function profile(): JsonResponse
    {
        try {
            $user = auth()->user();

            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(\App\Http\Requests\UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $this->userRepository->update(auth()->id(), $request->all());
            $this->loggingService->logAction('update_profile', 'user', $user->reference, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, 'UserController@updateProfile');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

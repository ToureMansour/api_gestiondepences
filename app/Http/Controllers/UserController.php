<?php

namespace App\Http\Controllers;

use App\Interfaces\UserRepositoryInterface;
use App\Services\LoggingService;
use App\Services\AuthService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected UserRepositoryInterface $userRepository;
    protected LoggingService $loggingService;
    protected AuthService $authService;
    protected NotificationService $notificationService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        LoggingService $loggingService,
        AuthService $authService,
        NotificationService $notificationService
    ) {
        $this->userRepository = $userRepository;
        $this->loggingService = $loggingService;
        $this->authService = $authService;
        $this->notificationService = $notificationService;
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:8',
                'role' => 'nullable|in:employee,manager,admin',
            ]);

            $result = $this->authService->register($request->all());
            $this->notificationService->onUserRegistered($result['user']);
            $this->loggingService->logAction('create', 'user', $result['user']->reference, auth()->id(), ['email' => $result['user']->email]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $result['user']
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, 'UserController@store');
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
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

    public function update(Request $request, string $userReference): JsonResponse
    {
        try {
            $user = $this->userRepository->findByReference($userReference);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $request->validate([
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:8',
                'role' => 'nullable|in:employee,manager,admin',
            ]);

            $data = $request->only(['name', 'email', 'role']);
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $updatedUser = $this->userRepository->update($user->id, $data);
            $this->loggingService->logAction('update', 'user', $updatedUser->reference, auth()->id(), ['email' => $updatedUser->email]);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $updatedUser
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, 'UserController@update');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $userReference): JsonResponse
    {
        try {
            $user = $this->userRepository->findByReference($userReference);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], 422);
            }

            $deleted = $this->userRepository->deleteByReference($userReference);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete user'
                ], 500);
            }

            $this->loggingService->logAction('delete', 'user', $userReference, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, 'UserController@destroy');
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
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

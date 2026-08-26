<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AdminExpenseController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth routes (public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);

    // Users (admin)
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{userReference}', [UserController::class, 'show'])->middleware('uuid:userReference');
    });

    // Expense routes
    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::get('/expenses/{expenseReference}', [ExpenseController::class, 'show'])->middleware('uuid:expenseReference');
    Route::put('/expenses/{expenseReference}', [ExpenseController::class, 'update'])->middleware('uuid:expenseReference');
    Route::delete('/expenses/{expenseReference}', [ExpenseController::class, 'destroy'])->middleware('uuid:expenseReference');

    // Admin expense actions
    Route::middleware('role:admin')->prefix('expenses/{expenseReference}')->middleware('uuid:expenseReference')->group(function () {
        Route::post('/approve', [AdminExpenseController::class, 'approve']);
        Route::post('/reject', [AdminExpenseController::class, 'reject']);
        Route::post('/pay', [AdminExpenseController::class, 'pay']);
    });

    // Stats routes
    Route::get('/stats', [StatsController::class, 'index']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::middleware('role:admin')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    // Settings (admin)
    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index']);
        Route::put('/settings', [SettingsController::class, 'update']);
    });
});

// Legacy user route
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

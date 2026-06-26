<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AdminExpenseController;
use App\Http\Controllers\StatsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// User routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    
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
});

// Legacy user route for compatibility
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

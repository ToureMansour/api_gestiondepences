<?php

namespace App\Http\Controllers;

use App\Services\ExpenseService;
use App\Services\LoggingService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExpenseController extends Controller
{
    protected ExpenseService $expenseService;
    protected LoggingService $loggingService;
    protected NotificationService $notificationService;

    public function __construct(ExpenseService $expenseService, LoggingService $loggingService, NotificationService $notificationService)
    {
        $this->expenseService = $expenseService;
        $this->loggingService = $loggingService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'per_page',
                'status',
                'user_id',
                'date_from',
                'date_to',
                'amount_min',
                'amount_max'
            ]);

            if (auth()->user()->isAdmin()) {
                $result = $this->expenseService->getAllExpenses($filters);
            } else {
                $filters['user_id'] = auth()->id();
                $result = $this->expenseService->getUserExpenses(auth()->id(), $filters);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['expenses']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve expenses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(\App\Http\Requests\CreateExpenseRequest $request): JsonResponse
    {
        try {
            $result = $this->expenseService->createExpense(
                $request->except('proof'),
                $request->file('proof'),
                auth()->id()
            );
            $this->loggingService->logAction('create', 'expense', $result['expense']->reference, auth()->id());
            $this->notificationService->onExpenseCreated($result['expense']);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['expense']
            ], 201);
        } catch (\InvalidArgumentException $e) {
            $this->loggingService->logValidationError('/api/expenses', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, 'ExpenseController@store');
            return response()->json([
                'success' => false,
                'message' => 'Failed to create expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $expenseReference): JsonResponse
    {
        try {
            $result = $this->expenseService->getExpenseById($expenseReference);

            if (auth()->user()->isEmployee() && $result['expense']->user_id !== auth()->id()) {
                $this->loggingService->logWarning('Unauthorized access attempt', ['expense_reference' => $expenseReference, 'user_id' => auth()->id()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $this->loggingService->logAction('view', 'expense', $expenseReference, auth()->id());
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['expense']
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->loggingService->logWarning('Expense not found', ['expense_reference' => $expenseReference]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, 'ExpenseController@show');
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(\App\Http\Requests\UpdateExpenseRequest $request, string $expenseReference): JsonResponse
    {
        try {
            $result = $this->expenseService->updateExpense(
                $expenseReference,
                $request->all(),
                auth()->id()
            );
            $this->loggingService->logAction('update', 'expense', $expenseReference, auth()->id());

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['expense']
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->loggingService->logValidationError('/api/expenses/' . $expenseReference, ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, 'ExpenseController@update');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $expenseReference): JsonResponse
    {
        try {
            $result = $this->expenseService->cancelExpense($expenseReference, auth()->id());
            $this->loggingService->logAction('cancel', 'expense', $expenseReference, auth()->id());

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['expense']
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->loggingService->logValidationError('/api/expenses/' . $expenseReference, ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, 'ExpenseController@destroy');
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

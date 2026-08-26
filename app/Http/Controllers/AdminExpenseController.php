<?php

namespace App\Http\Controllers;

use App\Services\ExpenseService;
use App\Services\LoggingService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminExpenseController extends Controller
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

    public function approve(string $expenseReference): JsonResponse
    {
        try {
            $result = $this->expenseService->approveExpense($expenseReference);
            $this->loggingService->logAction('approve', 'expense', $expenseReference, auth()->id());
            $this->notificationService->onExpenseApproved($result['expense']);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['expense']
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->loggingService->logValidationError('/api/expenses/' . $expenseReference . '/approve', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, 'AdminExpenseController@approve');
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, string $expenseReference): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $result = $this->expenseService->rejectExpense($expenseReference, $request->reason);
            $this->loggingService->logAction('reject', 'expense', $expenseReference, auth()->id(), ['reason' => $request->reason]);
            $this->notificationService->onExpenseRejected($result['expense'], $request->reason);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['expense']
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->loggingService->logValidationError('/api/expenses/' . $expenseReference . '/reject', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, 'AdminExpenseController@reject');
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function pay(Request $request, string $expenseReference): JsonResponse
    {
        $request->validate([
            'payment_method' => 'required|in:cash,mobile_money,transfer',
            'reference' => 'nullable|string|max:255',
            'paid_at' => 'nullable|date',
        ]);

        try {
            $result = $this->expenseService->markAsPaid($expenseReference, $request->all());
            $this->loggingService->logAction('pay', 'expense', $expenseReference, auth()->id(), ['payment_method' => $request->payment_method]);
            $this->notificationService->onExpensePaid($result['expense']);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['expense']
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->loggingService->logValidationError('/api/expenses/' . $expenseReference . '/pay', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, 'AdminExpenseController@pay');
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark expense as paid',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

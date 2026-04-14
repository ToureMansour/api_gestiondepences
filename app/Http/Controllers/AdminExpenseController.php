<?php

namespace App\Http\Controllers;

use App\Services\ExpenseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminExpenseController extends Controller
{
    protected ExpenseService $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin');
    }

    public function approve(int $id): JsonResponse
    {
        try {
            $result = $this->expenseService->approveExpense($id);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['expense']
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $result = $this->expenseService->rejectExpense($id, $request->reason);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['expense']
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function pay(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'payment_method' => 'required|in:cash,mobile_money,transfer',
            'reference' => 'nullable|string|max:255',
            'paid_at' => 'nullable|date',
        ]);

        try {
            $result = $this->expenseService->markAsPaid($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['expense']
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark expense as paid',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

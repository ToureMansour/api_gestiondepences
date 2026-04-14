<?php

namespace App\Http\Controllers;

use App\Services\ExpenseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExpenseController extends Controller
{
    protected ExpenseService $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
        $this->middleware('auth:sanctum');
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

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'expense_date' => 'required|date',
            'proof' => 'required|image|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        try {
            $result = $this->expenseService->createExpense(
                $request->except('proof'),
                $request->file('proof'),
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['expense']
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->expenseService->getExpenseById($id);

            if (auth()->user()->isEmployee() && $result['expense']->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['expense']
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'description' => 'nullable|string',
            'expense_date' => 'sometimes|required|date',
        ]);

        try {
            $result = $this->expenseService->updateExpense(
                $id,
                $request->all(),
                auth()->id()
            );

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
                'message' => 'Failed to update expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->expenseService->cancelExpense($id, auth()->id());

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
                'message' => 'Failed to cancel expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Services;

use App\Interfaces\ExpenseRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ExpenseService
{
    protected ExpenseRepositoryInterface $expenseRepository;
    protected UserRepositoryInterface $userRepository;

    public function __construct(
        ExpenseRepositoryInterface $expenseRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->expenseRepository = $expenseRepository;
        $this->userRepository = $userRepository;
    }

    public function createExpense(array $data, UploadedFile $proof, int $userId): array
    {
        $this->validateExpenseData($data);
        
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $proofPath = $this->storeProofFile($proof);

        $expenseData = array_merge($data, [
            'user_id' => $userId,
            'proof_file_path' => $proofPath,
            'status' => 'PENDING',
        ]);

        $expense = $this->expenseRepository->create($expenseData);

        return [
            'expense' => $expense->load('user'),
            'message' => 'Expense created successfully',
        ];
    }

    public function updateExpense(int $expenseId, array $data, int $userId): array
    {
        $expense = $this->expenseRepository->findById($expenseId);
        
        if (!$expense) {
            throw new \InvalidArgumentException('Expense not found');
        }

        if ($expense->user_id !== $userId) {
            throw new \InvalidArgumentException('Unauthorized to update this expense');
        }

        if ($expense->status !== 'PENDING') {
            throw new \InvalidArgumentException('Cannot update expense with status: ' . $expense->status);
        }

        $this->validateExpenseData($data, true);

        $updatedExpense = $this->expenseRepository->update($expenseId, $data);

        return [
            'expense' => $updatedExpense->load('user'),
            'message' => 'Expense updated successfully',
        ];
    }

    public function cancelExpense(int $expenseId, int $userId): array
    {
        $expense = $this->expenseRepository->findById($expenseId);
        
        if (!$expense) {
            throw new \InvalidArgumentException('Expense not found');
        }

        if ($expense->user_id !== $userId) {
            throw new \InvalidArgumentException('Unauthorized to cancel this expense');
        }

        if ($expense->status !== 'PENDING') {
            throw new \InvalidArgumentException('Cannot cancel expense with status: ' . $expense->status);
        }

        $updatedExpense = $this->expenseRepository->update($expenseId, [
            'status' => 'CANCELLED',
        ]);

        return [
            'expense' => $updatedExpense->load('user'),
            'message' => 'Expense cancelled successfully',
        ];
    }

    public function approveExpense(int $expenseId): array
    {
        $expense = $this->expenseRepository->findById($expenseId);
        
        if (!$expense) {
            throw new \InvalidArgumentException('Expense not found');
        }

        if ($expense->status !== 'PENDING') {
            throw new \InvalidArgumentException('Cannot approve expense with status: ' . $expense->status);
        }

        $updatedExpense = $this->expenseRepository->update($expenseId, [
            'status' => 'APPROVED',
        ]);

        return [
            'expense' => $updatedExpense->load('user'),
            'message' => 'Expense approved successfully',
        ];
    }

    public function rejectExpense(int $expenseId, string $reason): array
    {
        $expense = $this->expenseRepository->findById($expenseId);
        
        if (!$expense) {
            throw new \InvalidArgumentException('Expense not found');
        }

        if ($expense->status !== 'PENDING') {
            throw new \InvalidArgumentException('Cannot reject expense with status: ' . $expense->status);
        }

        if (empty($reason)) {
            throw new \InvalidArgumentException('Rejection reason is required');
        }

        $updatedExpense = $this->expenseRepository->update($expenseId, [
            'status' => 'REJECTED',
            'rejection_reason' => $reason,
        ]);

        return [
            'expense' => $updatedExpense->load('user'),
            'message' => 'Expense rejected successfully',
        ];
    }

    public function markAsPaid(int $expenseId, array $paymentData): array
    {
        $expense = $this->expenseRepository->findById($expenseId);
        
        if (!$expense) {
            throw new \InvalidArgumentException('Expense not found');
        }

        if ($expense->status !== 'APPROVED') {
            throw new \InvalidArgumentException('Cannot mark as paid expense with status: ' . $expense->status);
        }

        $this->validatePaymentData($paymentData);

        $updatedExpense = $this->expenseRepository->update($expenseId, [
            'status' => 'PAID',
            'payment_method' => $paymentData['payment_method'],
            'payment_reference' => $paymentData['reference'] ?? null,
            'paid_at' => $paymentData['paid_at'] ?? now(),
        ]);

        return [
            'expense' => $updatedExpense->load('user'),
            'message' => 'Expense marked as paid successfully',
        ];
    }

    public function getUserExpenses(int $userId, array $filters = []): array
    {
        $perPage = $filters['per_page'] ?? 10;
        $expenses = $this->expenseRepository->getByUserIdPaginated($userId, $perPage);

        return [
            'expenses' => $expenses,
            'message' => 'User expenses retrieved successfully',
        ];
    }

    public function getAllExpenses(array $filters = []): array
    {
        $expenses = $this->expenseRepository->filter($filters);

        return [
            'expenses' => $expenses,
            'message' => 'All expenses retrieved successfully',
        ];
    }

    public function getExpenseById(int $expenseId): array
    {
        $expense = $this->expenseRepository->findById($expenseId);
        
        if (!$expense) {
            throw new \InvalidArgumentException('Expense not found');
        }

        return [
            'expense' => $expense,
            'message' => 'Expense retrieved successfully',
        ];
    }

    private function validateExpenseData(array $data, bool $isUpdate = false): void
    {
        if (!$isUpdate) {
            if (empty($data['title'])) {
                throw new \InvalidArgumentException('Title is required');
            }
            if (!isset($data['amount']) || $data['amount'] <= 0) {
                throw new \InvalidArgumentException('Amount must be greater than 0');
            }
            if (empty($data['expense_date'])) {
                throw new \InvalidArgumentException('Expense date is required');
            }
        } else {
            if (isset($data['amount']) && $data['amount'] <= 0) {
                throw new \InvalidArgumentException('Amount must be greater than 0');
            }
        }
    }

    private function validatePaymentData(array $data): void
    {
        if (empty($data['payment_method'])) {
            throw new \InvalidArgumentException('Payment method is required');
        }

        $validMethods = ['cash', 'mobile_money', 'transfer'];
        if (!in_array($data['payment_method'], $validMethods)) {
            throw new \InvalidArgumentException('Invalid payment method');
        }
    }

    private function storeProofFile(UploadedFile $file): string
    {
        $filename = 'expenses/' . uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        return $file->storeAs('public', $filename);
    }
}

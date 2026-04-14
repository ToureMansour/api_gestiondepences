<?php

namespace App\Services;

use App\Interfaces\ExpenseRepositoryInterface;

class StatsService
{
    protected ExpenseRepositoryInterface $expenseRepository;

    public function __construct(ExpenseRepositoryInterface $expenseRepository)
    {
        $this->expenseRepository = $expenseRepository;
    }

    public function getGeneralStats(): array
    {
        return [
            'total_expenses' => $this->expenseRepository->countByStatus('PENDING') + 
                               $this->expenseRepository->countByStatus('APPROVED') + 
                               $this->expenseRepository->countByStatus('REJECTED') + 
                               $this->expenseRepository->countByStatus('PAID') + 
                               $this->expenseRepository->countByStatus('CANCELLED'),
            'pending_expenses' => $this->expenseRepository->countByStatus('PENDING'),
            'approved_expenses' => $this->expenseRepository->countByStatus('APPROVED'),
            'rejected_expenses' => $this->expenseRepository->countByStatus('REJECTED'),
            'paid_expenses' => $this->expenseRepository->countByStatus('PAID'),
            'cancelled_expenses' => $this->expenseRepository->countByStatus('CANCELLED'),
            'total_amount_pending' => $this->expenseRepository->getTotalAmountByStatus('PENDING'),
            'total_amount_approved' => $this->expenseRepository->getTotalAmountByStatus('APPROVED'),
            'total_amount_paid' => $this->expenseRepository->getTotalAmountByStatus('PAID'),
        ];
    }

    public function getUserStats(int $userId): array
    {
        $userExpenses = $this->expenseRepository->getByUserId($userId);
        
        return [
            'total_expenses' => $userExpenses->count(),
            'pending_expenses' => $userExpenses->where('status', 'PENDING')->count(),
            'approved_expenses' => $userExpenses->where('status', 'APPROVED')->count(),
            'rejected_expenses' => $userExpenses->where('status', 'REJECTED')->count(),
            'paid_expenses' => $userExpenses->where('status', 'PAID')->count(),
            'cancelled_expenses' => $userExpenses->where('status', 'CANCELLED')->count(),
            'total_amount' => $userExpenses->sum('amount'),
            'total_amount_paid' => $userExpenses->where('status', 'PAID')->sum('amount'),
        ];
    }
}

<?php

namespace App\Repositories;

use App\Models\Expense;
use App\Interfaces\ExpenseRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class ExpenseRepository implements ExpenseRepositoryInterface
{
    protected Expense $model;

    public function __construct(Expense $expense)
    {
        $this->model = $expense;
    }

    public function findById(int $id): ?Expense
    {
        return $this->model->with('user')->find($id);
    }

    public function create(array $data): Expense
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Expense
    {
        $expense = $this->findById($id);
        $expense->update($data);
        return $expense;
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }

    public function getByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->get();
    }

    public function getByUserIdPaginated(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAllPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAll(): Collection
    {
        return $this->model->with('user')->get();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->with('user')
            ->get();
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with('user');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('expense_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('expense_date', '<=', $filters['date_to']);
        }

        if (isset($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }

        if (isset($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function countByStatus(string $status): int
    {
        return $this->model->where('status', $status)->count();
    }

    public function getTotalAmountByStatus(string $status): float
    {
        return $this->model->where('status', $status)->sum('amount');
    }
}

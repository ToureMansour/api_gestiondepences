<?php

namespace App\Interfaces;

use App\Models\Expense;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ExpenseRepositoryInterface
{
    public function findById(int $id): ?Expense;
    public function create(array $data): Expense;
    public function update(int $id, array $data): Expense;
    public function delete(int $id): bool;
    public function getByUserId(int $userId): Collection;
    public function getByUserIdPaginated(int $userId, int $perPage = 10): LengthAwarePaginator;
    public function getAllPaginated(int $perPage = 10): LengthAwarePaginator;
    public function getAll(): Collection;
    public function getByStatus(string $status): Collection;
    public function filter(array $filters): LengthAwarePaginator;
    public function countByStatus(string $status): int;
    public function getTotalAmountByStatus(string $status): float;
}

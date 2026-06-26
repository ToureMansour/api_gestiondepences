<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByReference(string $reference): ?User;
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
    public function update(int $id, array $data): User;
    public function updateByReference(string $reference, array $data): User;
    public function delete(int $id): bool;
    public function deleteByReference(string $reference): bool;
    public function getAllPaginated(int $perPage = 10): LengthAwarePaginator;
    public function getAll(): \Illuminate\Database\Eloquent\Collection;
}

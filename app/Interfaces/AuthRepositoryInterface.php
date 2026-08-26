<?php

namespace App\Interfaces;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
    public function updateLastLogin(int $userId): bool;
    public function updatePassword(int $userId, string $hashedPassword): bool;
    public function saveResetToken(string $email, string $hashedToken): void;
    public function getResetToken(string $email): ?object;
    public function deleteResetToken(string $email): void;
}

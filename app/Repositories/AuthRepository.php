<?php

namespace App\Repositories;

use App\Models\User;
use App\Interfaces\AuthRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AuthRepository implements AuthRepositoryInterface
{
    protected User $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function updateLastLogin(int $userId): bool
    {
        $user = $this->model->find($userId);
        if ($user) {
            $user->last_login_at = now();
            return $user->save();
        }
        return false;
    }

    public function updatePassword(int $userId, string $hashedPassword): bool
    {
        $user = $this->model->find($userId);
        if ($user) {
            $user->password = $hashedPassword;
            return $user->save();
        }
        return false;
    }

    public function saveResetToken(string $email, string $hashedToken): void
    {
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $hashedToken, 'created_at' => now()]
        );
    }

    public function getResetToken(string $email): ?object
    {
        return DB::table('password_reset_tokens')->where('email', $email)->first();
    }

    public function deleteResetToken(string $email): void
    {
        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }
}

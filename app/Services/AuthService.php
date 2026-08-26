<?php

namespace App\Services;

use App\Interfaces\AuthRepositoryInterface;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    protected AuthRepositoryInterface $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function register(array $data): array
    {
        $user = $this->authRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'employee',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            throw new \InvalidArgumentException('Invalid credentials');
        }

        $user = Auth::user();
        $this->authRepository->updateLastLogin($user->id);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(): void
    {
        Auth::user()->currentAccessToken()->delete();
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): void
    {
        $user = Auth::user();

        if (!Hash::check($currentPassword, $user->password)) {
            throw new \InvalidArgumentException('Le mot de passe actuel est incorrect');
        }

        $this->authRepository->updatePassword($userId, Hash::make($newPassword));
    }

    public function forgotPassword(string $email): void
    {
        $user = $this->authRepository->findByEmail($email);

        if (!$user) {
            return;
        }

        $token = \Illuminate\Support\Str::random(64);

        $this->authRepository->saveResetToken($email, Hash::make($token));

        Mail::to($email)->send(new ResetPasswordMail($token, $email));
    }

    public function resetPassword(string $email, string $token, string $password): void
    {
        $reset = $this->authRepository->getResetToken($email);

        if (!$reset || !Hash::check($token, $reset->token)) {
            throw new \InvalidArgumentException('Token de reinitialisation invalide');
        }

        if (now()->diffInMinutes($reset->created_at) > 60) {
            $this->authRepository->deleteResetToken($email);
            throw new \InvalidArgumentException('Token de reinitialisation expire');
        }

        $user = $this->authRepository->findByEmail($email);
        if (!$user) {
            throw new \InvalidArgumentException('Utilisateur introuvable');
        }

        $this->authRepository->updatePassword($user->id, Hash::make($password));
        $this->authRepository->deleteResetToken($email);
    }
}

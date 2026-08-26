<?php

namespace App\Services;

use App\Models\NotificationModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function notifyAdmins(string $type, string $title, string $message, ?array $data = null): void
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $this->create($admin->id, $type, $title, $message, $data);
        }
    }

    public function notifyUser(int $userId, string $type, string $title, string $message, ?array $data = null): void
    {
        $this->create($userId, $type, $title, $message, $data);
    }

    public function onExpenseCreated($expense): void
    {
        $amount = number_format($expense->amount, 2, '.', '');
        $this->notifyAdmins(
            'expense',
            'Nouvelle depense soumise',
            "Une depense de {$amount} EUR necessite votre validation.",
            ['expense_reference' => $expense->reference]
        );
    }

    public function onExpenseApproved($expense): void
    {
        $this->notifyUser(
            $expense->user_id,
            'approved',
            'Depense approuvee',
            "Votre depense {$expense->reference} a ete approuvee.",
            ['expense_reference' => $expense->reference]
        );
    }

    public function onExpenseRejected($expense, string $reason): void
    {
        $this->notifyUser(
            $expense->user_id,
            'rejected',
            'Depense rejetee',
            "Votre depense {$expense->reference} a ete rejetee. Raison: {$reason}",
            ['expense_reference' => $expense->reference, 'reason' => $reason]
        );
    }

    public function onExpensePaid($expense): void
    {
        $this->notifyUser(
            $expense->user_id,
            'paid',
            'Depense payee',
            "Votre depense {$expense->reference} a ete marquee comme payee.",
            ['expense_reference' => $expense->reference]
        );
    }

    public function onUserRegistered(User $user): void
    {
        $this->notifyAdmins(
            'user',
            'Nouvel utilisateur',
            "{$user->name} a rejoint l organisation.",
            ['user_reference' => $user->reference]
        );
    }

    public function getForUser(int $userId)
    {
        return NotificationModel::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function markRead(int $notificationId, int $userId): void
    {
        NotificationModel::where('id', $notificationId)
            ->where('user_id', $userId)
            ->update(['read_at' => now()]);
    }

    public function markAllRead(int $userId): void
    {
        NotificationModel::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    private function create(int $userId, string $type, string $title, string $message, ?array $data = null): NotificationModel
    {
        return NotificationModel::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }
}

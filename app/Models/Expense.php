<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'amount',
        'description',
        'proof_file_path',
        'status',
        'rejection_reason',
        'payment_method',
        'payment_reference',
        'paid_at',
        'expense_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    public function isRejected(): bool
    {
        return $this->status === 'REJECTED';
    }

    public function isPaid(): bool
    {
        return $this->status === 'PAID';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'CANCELLED';
    }

    public function canBeModified(): bool
    {
        return $this->status === 'PENDING';
    }

    public function canBeCancelled(): bool
    {
        return $this->status === 'PENDING';
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'PENDING';
    }

    public function canBeRejected(): bool
    {
        return $this->status === 'PENDING';
    }

    public function canBePaid(): bool
    {
        return $this->status === 'APPROVED';
    }

    public function getProofUrlAttribute(): string
    {
        return asset('storage/' . $this->proof_file_path);
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'payment_method' => 'required|in:cash,mobile_money,transfer',
            'reference' => 'nullable|string|max:255',
            'paid_at' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'La méthode de paiement est obligatoire',
            'payment_method.in' => 'La méthode de paiement doit être cash, mobile_money ou transfer',
            'reference.max' => 'La référence ne doit pas dépasser 255 caractères',
            'paid_at.date' => 'La date de paiement doit être valide',
        ];
    }
}

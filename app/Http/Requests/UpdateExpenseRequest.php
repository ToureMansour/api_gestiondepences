<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $expense = $this->route('expense');
        return auth()->check() && 
               $expense && 
               $expense->user_id === auth()->id() && 
               $expense->status === 'PENDING';
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0.01|max:999999.99',
            'description' => 'nullable|string|max:1000',
            'expense_date' => 'sometimes|required|date|before_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire',
            'title.max' => 'Le titre ne doit pas dépasser 255 caractères',
            'amount.required' => 'Le montant est obligatoire',
            'amount.numeric' => 'Le montant doit être un nombre',
            'amount.min' => 'Le montant doit être supérieur à 0',
            'amount.max' => 'Le montant ne peut pas dépasser 999999.99',
            'expense_date.required' => 'La date de dépense est obligatoire',
            'expense_date.before_or_equal' => 'La date de dépense ne peut pas être dans le futur',
        ];
    }
}

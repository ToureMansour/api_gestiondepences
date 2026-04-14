<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'description' => 'nullable|string|max:1000',
            'expense_date' => 'required|date|before_or_equal:today',
            'proof' => 'required|file|mimes:jpeg,jpg,png,pdf|max:2048',
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
            'proof.required' => 'Le justificatif est obligatoire',
            'proof.mimes' => 'Le justificatif doit être un fichier (jpeg, jpg, png, pdf)',
            'proof.max' => 'Le justificatif ne doit pas dépasser 2MB',
        ];
    }
}

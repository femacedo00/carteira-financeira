<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class TransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        if ($this->has('cpf_cnpj')) {
            $this->merge([
                'cpf_cnpj' => $this->sanitizeCpfCnpj($this->cpf_cnpj),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|gt:0|decimal:0,2',
            'addressee_document' => 'required|string|cpf_cnpj',
            'financial_password' => 'required|string|numeric|digits:6',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'cpf_cnpj' => 'Invalid CPF or CNPJ format.',
        ];
    }
}

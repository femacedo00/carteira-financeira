<?php

namespace App\Http\Requests;

use App\Traits\SanitizesDocument;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreUserRequest extends FormRequest
{
    use SanitizesDocument;

    /**
     * Determine if user is authorized to make this request.
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
            'name' => 'required|string|max:255',
            'cpf_cnpj' => 'required|string|cpf_cnpj|max:14|unique:users,cpf_cnpj',
            'password' => 'required|string|min:8',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'cpf_cnpj.cpf_cnpj' => 'Invalid CPF or CNPJ format.',
            'unique' => 'This document is already registered.',
        ];
    }
}

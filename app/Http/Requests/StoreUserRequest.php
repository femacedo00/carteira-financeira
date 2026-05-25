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
     * Verify if user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        if ($this->has('document')) {
            $this->merge([
                'document' => $this->sanitizeCpfCnpj($this->document),
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
            'username' => 'required|string|max:255',
            'document' => 'required|string|cpf_cnpj|max:14|unique:users,cpf_cnpj',
            'password' => 'required|string|min:8',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'cpf_cnpj' => 'Invalid CPF or CNPJ format.',
            'unique' => 'This document is already registered.',
        ];
    }
}

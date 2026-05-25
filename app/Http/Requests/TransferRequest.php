<?php

namespace App\Http\Requests;

use App\Traits\SanitizesDocument;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class TransferRequest extends FormRequest
{
    use SanitizesDocument;

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
        if ($this->has('addressee_document')) {
            $this->merge([
                'addressee_document' => $this->sanitizeCpfCnpj($this->addressee_document),
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
            'password' => 'required|string|numeric|digits:6',
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

<?php

namespace App\Traits;

trait SanitizesDocument
{
    /**
     * Removing everything except numbers
     */
    protected function sanitizeCpfCnpj(?string $cpf_cnpj): ?string
    {
        if (! $cpf_cnpj) {
            return null;
        }

        return preg_replace('/[^0-9]/', '', $cpf_cnpj);
    }
}

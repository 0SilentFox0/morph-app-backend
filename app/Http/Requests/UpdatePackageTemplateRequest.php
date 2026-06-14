<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'kind' => ['nullable', 'in:count_based,time_based,hybrid'],
            'sessions_count' => ['nullable', 'integer', 'min:1'],
            'validity_days' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'auto_renew_default' => ['nullable', 'boolean'],
        ];
    }
}

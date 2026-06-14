<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignClientPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'uuid', 'exists:clients,id'],
            'template_id' => ['nullable', 'uuid'],
            'kind' => ['required', 'in:count_based,time_based,hybrid'],
            'sessions_count' => ['nullable', 'integer'],
            'validity_days' => ['nullable', 'integer'],
            'price' => ['required', 'numeric'],
            'currency' => ['required', 'string', 'max:3'],
            'auto_renew' => ['nullable', 'boolean'],
        ];
    }
}

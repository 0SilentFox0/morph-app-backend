<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'kind' => ['required', 'in:count_based,time_based,hybrid'],
            'sessions_count' => ['nullable', 'integer', 'min:1', 'required_if:kind,count_based', 'required_if:kind,hybrid'],
            'validity_days' => ['nullable', 'integer', 'min:1', 'required_if:kind,time_based', 'required_if:kind,hybrid'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'auto_renew_default' => ['nullable', 'boolean'],
        ];
    }
}

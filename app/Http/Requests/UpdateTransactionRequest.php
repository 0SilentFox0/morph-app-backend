<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'uuid'],
            'client_package_id' => ['nullable', 'uuid'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'max:3'],
            'method' => ['nullable', 'in:cash,transfer,card,other'],
            'status' => ['nullable', 'in:paid,pending,canceled'],
            'paid_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ];
    }
}

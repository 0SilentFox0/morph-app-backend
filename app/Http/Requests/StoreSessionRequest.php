<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title'           => ['required', 'string', 'max:255'],
            'type'            => ['nullable', 'string', 'max:50'],
            'start_at'        => ['required', 'date'],
            'end_at'          => ['required', 'date', 'after:start_at'],
            'notes'           => ['nullable', 'string'],
            'program_id'      => ['nullable', 'uuid'],
            'client_ids'      => ['required', 'array'],
            'client_ids.*'    => ['uuid'],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ];
    }
}

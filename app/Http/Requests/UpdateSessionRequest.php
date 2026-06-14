<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSessionRequest extends FormRequest
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
            'title'        => ['sometimes', 'string', 'max:255'],
            'type'         => ['nullable', 'string', 'max:50'],
            'start_at'     => ['sometimes', 'date'],
            'end_at'       => ['sometimes', 'date', 'after:start_at'],
            'notes'        => ['nullable', 'string'],
            'program_id'   => ['nullable', 'uuid'],
            'client_ids'   => ['sometimes', 'array'],
            'client_ids.*' => ['uuid'],
        ];
    }
}

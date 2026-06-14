<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSessionStatusRequest extends FormRequest
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
            'status'              => ['required', 'in:planned,in_progress,completed,canceled,no_show'],
            'cancellation_reason' => ['nullable', 'string', 'required_if:status,canceled,no_show'],
        ];
    }
}

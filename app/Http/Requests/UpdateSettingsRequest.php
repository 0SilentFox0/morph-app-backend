<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
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
            'timezone'                     => ['nullable', 'string', 'timezone'],
            'locale'                       => ['nullable', 'string', 'max:10'],
            'currency'                     => ['nullable', 'string', 'size:3'],
            'notification_preferences'     => ['nullable', 'array'],
        ];
    }
}

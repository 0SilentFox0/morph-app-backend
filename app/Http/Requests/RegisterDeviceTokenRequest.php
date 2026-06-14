<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterDeviceTokenRequest extends FormRequest
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
            'token'        => ['required', 'string'],
            'platform'     => ['required', 'string', 'in:ios,android'],
            'device_label' => ['nullable', 'string', 'max:255'],
            'app_version'  => ['nullable', 'string', 'max:50'],
        ];
    }
}

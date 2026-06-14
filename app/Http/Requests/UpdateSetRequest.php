<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSetRequest extends FormRequest
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
            'reps'         => ['nullable', 'integer'],
            'weight_kg'    => ['nullable', 'numeric'],
            'rest_seconds' => ['nullable', 'integer'],
        ];
    }
}

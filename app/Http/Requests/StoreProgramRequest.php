<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'difficulty' => ['nullable', 'in:beginner,intermediate,advanced'],
            'estimated_duration_min' => ['nullable', 'integer'],
            'cover_file_id' => ['nullable', 'uuid'],
            'exercises' => ['nullable', 'array'],
            'exercises.*.exercise_id' => ['required_with:exercises', 'uuid'],
            'exercises.*.order' => ['required_with:exercises', 'integer'],
            'exercises.*.sets' => ['required_with:exercises', 'integer', 'min:1', 'max:50'],
            'exercises.*.reps' => ['required_with:exercises', 'integer', 'min:1', 'max:1000'],
            'exercises.*.weight_kg' => ['nullable', 'numeric'],
            'exercises.*.rest_seconds' => ['nullable', 'integer'],
            'exercises.*.notes' => ['nullable', 'string'],
        ];
    }
}

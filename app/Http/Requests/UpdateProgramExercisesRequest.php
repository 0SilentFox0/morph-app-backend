<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgramExercisesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exercises' => ['required', 'array'],
            'exercises.*.exercise_id' => ['required', 'uuid'],
            'exercises.*.order' => ['required', 'integer'],
            'exercises.*.sets' => ['required', 'integer', 'min:1', 'max:50'],
            'exercises.*.reps' => ['required', 'integer', 'min:1', 'max:1000'],
            'exercises.*.weight_kg' => ['nullable', 'numeric'],
            'exercises.*.rest_seconds' => ['nullable', 'integer'],
            'exercises.*.notes' => ['nullable', 'string'],
        ];
    }
}

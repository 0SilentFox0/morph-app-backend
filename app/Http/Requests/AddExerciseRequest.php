<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddExerciseRequest extends FormRequest
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
            'exercise_id'       => ['required', 'uuid'],
            'name_snapshot'     => ['required', 'string'],
            'planned_sets'      => ['nullable', 'integer'],
            'planned_reps'      => ['nullable', 'integer'],
            'planned_weight_kg' => ['nullable', 'numeric'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LogSetRequest extends FormRequest
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
            'workout_log_exercise_id' => ['required', 'uuid'],
            'exercise_id'             => ['required', 'uuid'],
            'set_index'               => ['required', 'integer', 'min:1'],
            'reps'                    => ['required', 'integer', 'min:0', 'max:1000'],
            'weight_kg'               => ['required', 'numeric', 'min:0'],
            'rest_seconds'            => ['nullable', 'integer'],
            'client_uuid'             => ['required', 'uuid'],
        ];
    }
}

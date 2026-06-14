<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name'                 => ['nullable', 'string', 'max:255'],
            'experience'           => ['nullable', 'string', 'max:1000'],
            'certifications'       => ['nullable', 'array'],
            'certifications.*'     => ['string', 'max:255'],
            'training_types'       => ['nullable', 'array'],
            'training_types.*'     => ['string', 'max:255'],
            'client_types'         => ['nullable', 'array'],
            'client_types.*'       => ['string', 'max:255'],
            'locations'            => ['nullable', 'array'],
            'locations.*'          => ['string', 'max:255'],
            'work_schedule_start'  => ['nullable', 'date_format:H:i'],
            'work_schedule_end'    => ['nullable', 'date_format:H:i'],
            'work_schedule_days'   => ['nullable', 'array'],
            'work_schedule_days.*' => ['string', 'in:mon,tue,wed,thu,fri,sat,sun'],
            'goals'                => ['nullable', 'array'],
            'goals.*'              => ['string', 'max:255'],
            'fitness_level'        => ['nullable', 'string', 'in:beginner,intermediate,advanced,elite'],
        ];
    }
}

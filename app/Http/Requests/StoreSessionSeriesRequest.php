<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSessionSeriesRequest extends FormRequest
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
            'title'                          => ['required', 'string', 'max:255'],
            'type'                           => ['nullable', 'string', 'max:50'],
            'duration_minutes'               => ['required', 'integer', 'min:1'],
            'client_ids'                     => ['required', 'array'],
            'client_ids.*'                   => ['uuid'],
            'program_id'                     => ['nullable', 'uuid'],
            'recurrence_rule'                => ['required', 'array'],
            'recurrence_rule.frequency'      => ['required', 'string'],
            'recurrence_rule.days_of_week'   => ['required', 'array'],
            'recurrence_rule.days_of_week.*' => ['string'],
            'recurrence_rule.time'           => ['required', 'string'],
            'recurrence_rule.until_date'     => ['required', 'date'],
            'timezone'                       => ['required', 'string'],
        ];
    }
}

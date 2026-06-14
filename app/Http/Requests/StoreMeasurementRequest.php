<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeasurementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'metric_type' => ['required', 'in:weight,height,body_fat_percent,chest,waist,hips,biceps,thigh'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['required', 'string', 'max:8'],
            'measured_at' => ['required', 'date'],
        ];
    }
}

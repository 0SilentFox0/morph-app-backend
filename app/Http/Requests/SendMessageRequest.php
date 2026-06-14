<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
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
            'body'             => ['nullable', 'string', 'max:5000'],
            'media_file_ids'   => ['nullable', 'array'],
            'media_file_ids.*' => ['uuid'],
            'client_message_id' => ['required', 'uuid'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.required_without' => 'Either body or media_file_ids must be provided.',
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $body = $this->input('body');
                $mediaFileIds = $this->input('media_file_ids');

                if (empty($body) && empty($mediaFileIds)) {
                    $validator->errors()->add('body', 'Either body or media_file_ids must be provided.');
                }
            },
        ];
    }
}

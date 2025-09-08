<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB max
                'mimes:jpeg,jpg,png,gif,webp,svg,pdf,doc,docx,mp4,avi,mov,webm,mp3,wav,ogg',
            ],
            'files' => ['sometimes', 'array', 'max:10'],
            'files.*' => [
                'file',
                'max:10240', // 10MB max per file
                'mimes:jpeg,jpg,png,gif,webp,svg,pdf,doc,docx,mp4,avi,mov,webm,mp3,wav,ogg',
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.max' => 'The file must not be larger than 10MB.',
            'file.mimes' => 'The file must be a valid image, document, video, or audio file.',
            'files.max' => 'You can upload a maximum of 10 files at once.',
            'files.*.max' => 'Each file must not be larger than 10MB.',
            'files.*.mimes' => 'Each file must be a valid image, document, video, or audio file.',
            'name.max' => 'The name must not exceed 255 characters.',
            'alt_text.max' => 'The alt text must not exceed 500 characters.',
            'description.max' => 'The description must not exceed 1000 characters.',
        ];
    }
}

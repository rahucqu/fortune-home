<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'content' => ['required', 'string', 'min:3', 'max:5000'],
            'post_id' => ['required', 'exists:posts,id'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ];

        // If user is not authenticated, require guest information
        if (!Auth::check()) {
            $rules['author_name'] = ['required', 'string', 'max:255'];
            $rules['author_email'] = ['required', 'email', 'max:255'];
            $rules['author_website'] = ['nullable', 'url', 'max:255'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Comment content is required.',
            'content.min' => 'Comment must be at least 3 characters long.',
            'content.max' => 'Comment cannot exceed 5000 characters.',
            'post_id.required' => 'Post ID is required.',
            'post_id.exists' => 'The selected post does not exist.',
            'parent_id.exists' => 'The parent comment does not exist.',
            'author_name.required' => 'Your name is required.',
            'author_name.max' => 'Name cannot exceed 255 characters.',
            'author_email.required' => 'Your email is required.',
            'author_email.email' => 'Please provide a valid email address.',
            'author_email.max' => 'Email cannot exceed 255 characters.',
            'author_website.url' => 'Please provide a valid website URL.',
            'author_website.max' => 'Website URL cannot exceed 255 characters.',
        ];
    }

    public function prepareForValidation(): void
    {
        // Add user ID if authenticated
        if (Auth::check()) {
            $this->merge([
                'user_id' => Auth::id(),
            ]);
        }

        // Add IP address and user agent for security tracking
        $this->merge([
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function validatedData(): array
    {
        $validated = $this->validated();

        // Add default status
        $validated['status'] = 'pending';

        // Clean up unnecessary fields based on user authentication
        if (Auth::check()) {
            unset($validated['author_name'], $validated['author_email'], $validated['author_website']);
        }

        return $validated;
    }
}

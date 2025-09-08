<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $postId = $this->route('post') ? $this->route('post')->id : null;
        
        return [
            'title' => 'required|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('posts', 'slug')->ignore($postId),
            ],
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,scheduled,archived',
            'published_at' => 'nullable|date',
            'scheduled_at' => 'nullable|date|after:now',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'is_sticky' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'featured_image_id' => 'nullable|exists:media,id',
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:tags,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The post title is required.',
            'title.max' => 'The post title cannot exceed 255 characters.',
            'slug.unique' => 'This slug is already taken. Please choose a different one.',
            'slug.max' => 'The slug cannot exceed 255 characters.',
            'excerpt.max' => 'The excerpt cannot exceed 1000 characters.',
            'meta_title.max' => 'The meta title cannot exceed 255 characters.',
            'meta_description.max' => 'The meta description cannot exceed 500 characters.',
            'meta_keywords.max' => 'The meta keywords cannot exceed 255 characters.',
            'status.required' => 'Please select a post status.',
            'status.in' => 'Please select a valid post status.',
            'published_at.date' => 'Please enter a valid publication date.',
            'scheduled_at.date' => 'Please enter a valid scheduled date.',
            'scheduled_at.after' => 'The scheduled date must be in the future.',
            'category_id.exists' => 'The selected category does not exist.',
            'featured_image_id.exists' => 'The selected featured image does not exist.',
            'tag_ids.array' => 'Tags must be provided as an array.',
            'tag_ids.*.exists' => 'One or more selected tags do not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'meta_title' => 'SEO title',
            'meta_description' => 'SEO description',
            'meta_keywords' => 'SEO keywords',
            'category_id' => 'category',
            'featured_image_id' => 'featured image',
            'tag_ids' => 'tags',
            'is_featured' => 'featured status',
            'allow_comments' => 'comment settings',
            'is_sticky' => 'sticky status',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox values to boolean
        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
            'allow_comments' => $this->boolean('allow_comments', true), // Default to true
            'is_sticky' => $this->boolean('is_sticky'),
        ]);

        // Handle empty category selection
        if ($this->category_id === '') {
            $this->merge(['category_id' => null]);
        }

        // Handle empty featured image selection
        if ($this->featured_image_id === '') {
            $this->merge(['featured_image_id' => null]);
        }

        // Ensure tag_ids is an array
        if (!is_array($this->tag_ids)) {
            $this->merge(['tag_ids' => []]);
        }
    }
}

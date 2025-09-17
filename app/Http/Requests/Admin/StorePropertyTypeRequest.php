<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePropertyTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return $this->user()->can('create property types');
        return true;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:property_types,name',
            'slug' => 'nullable|string|max:255|unique:property_types,slug',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Property type name is required.',
            'name.unique' => 'This property type name already exists.',
            'slug.unique' => 'This property type slug already exists.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Generate slug if not provided
        if (! $this->has('slug') || empty($this->slug)) {
            $this->merge([
                'slug' => str($this->name)->slug(),
            ]);
        }

        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}

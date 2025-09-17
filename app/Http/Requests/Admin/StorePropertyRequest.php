<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return $this->user()->can('create properties');
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
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:properties,slug',
            'description' => 'required|string|max:5000',
            'price' => 'required|numeric|min:0',
            'property_type_id' => 'required|exists:property_types,id',
            'location_id' => 'required|exists:locations,id',
            'agent_id' => 'required|exists:agents,id',
            'status' => 'required|string|in:available,sold,rented,pending,draft',
            'listing_type' => 'required|string|in:sale,rent',

            // Property details
            'bedrooms' => 'nullable|integer|min:0|max:20',
            'bathrooms' => 'nullable|integer|min:0|max:20',
            'area_sqft' => 'nullable|numeric|min:0',
            'land_area_sqft' => 'nullable|numeric|min:0',
            'built_year' => 'nullable|integer|min:1800|max:' . (date('Y') + 5),
            'floors' => 'nullable|integer|min:1|max:10',
            'parking_spaces' => 'nullable|integer|min:0|max:20',

            // Location details
            'address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'postal_code' => 'nullable|string|max:20',

            // Amenities
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',

            // Features
            'is_furnished' => 'boolean',
            'has_parking' => 'boolean',
            'pet_friendly' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Property title is required.',
            'description.required' => 'Property description is required.',
            'price.required' => 'Property price is required.',
            'price.numeric' => 'Property price must be a valid number.',
            'property_type_id.required' => 'Property type is required.',
            'property_type_id.exists' => 'Selected property type is invalid.',
            'location_id.required' => 'Location is required.',
            'location_id.exists' => 'Selected location is invalid.',
            'agent_id.required' => 'Agent is required.',
            'agent_id.exists' => 'Selected agent is invalid.',
            'status.required' => 'Property status is required.',
            'listing_type.required' => 'Listing type is required.',
            'address.required' => 'Property address is required.',
            'built_year.max' => 'Year built cannot be more than 5 years in the future.',
        ];
    }
}

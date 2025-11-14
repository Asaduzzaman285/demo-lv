<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShopifyProductRequest extends FormRequest
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
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'variations' => 'required|array|min:1',
            'variations.*.title' => 'required|string|max:255',
            'variations.*.price' => 'required|numeric|min:0',
            'variations.*.inventory_quantity' => 'nullable|integer|min:0',
            'variations.*.images' => 'nullable|array',
            'variations.*.images.*.src' => 'required_with:variations.*.images|url',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The product title is required.',
            'variations.required' => 'At least one product variation is required.',
            'variations.*.title.required' => 'Each variation must have a title (e.g., "Red / Small").',
            'variations.*.price.required' => 'Each variation must have a price.',
            'variations.*.price.numeric' => 'The price must be a valid number.',
            'variations.*.inventory_quantity.integer' => 'Inventory quantity must be a whole number.',
            'variations.*.images.*.src.url' => 'Each image source must be a valid URL.',
        ];
    }
}
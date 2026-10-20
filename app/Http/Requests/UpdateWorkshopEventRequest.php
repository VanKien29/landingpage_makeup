<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkshopEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage-academy') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'tagline' => ['required', 'string', 'max:255'],
            'introduction' => ['required', 'string', 'max:5000'],
            'event_date' => ['nullable', 'date_format:Y-m-d'],
            'venue' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:60'],
            'registration_open' => ['required', 'boolean'],
            'hero_image' => ['required', Rule::in(self::imageOptions())],
            'glam_image' => ['required', Rule::in(self::imageOptions())],
            'bridal_image' => ['required', Rule::in(self::imageOptions())],
        ];
    }

    public static function imageOptions(): array
    {
        return array_map('basename', glob(public_path('images/*.{jpg,jpeg,png,webp}'), GLOB_BRACE) ?: []);
    }
}

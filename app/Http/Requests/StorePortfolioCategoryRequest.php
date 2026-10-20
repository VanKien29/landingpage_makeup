<?php

namespace App\Http\Requests;

use App\Models\PortfolioCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePortfolioCategoryRequest extends FormRequest
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
        return ['name' => ['required', 'string', 'max:80'], 'slug' => ['required', 'alpha_dash', 'max:80', Rule::unique(PortfolioCategory::class, 'slug')], 'display_order' => ['required', 'integer', 'min:0', 'max:10000'], 'is_active' => ['sometimes', 'boolean']];
    }
}

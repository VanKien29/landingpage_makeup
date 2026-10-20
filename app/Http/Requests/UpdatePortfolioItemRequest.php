<?php

namespace App\Http\Requests;

use App\Models\PortfolioCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePortfolioItemRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::exists(PortfolioCategory::class, 'slug')->where(fn ($query) => $query->where('is_active', true)->orWhere('slug', $this->route('portfolioItem')?->category))],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'display_order' => ['required', 'integer', 'min:0', 'max:10000'],
            'is_featured' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['category.exists' => 'Vui lòng chọn danh mục đang hoạt động.', 'image.image' => 'Tệp tải lên phải là hình ảnh.', 'image.mimes' => 'Chỉ hỗ trợ ảnh JPG, PNG hoặc WebP.', 'image.max' => 'Ảnh tối đa 8 MB.'];
    }
}

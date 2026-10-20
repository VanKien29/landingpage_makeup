<?php

namespace App\Http\Requests;

use App\Models\PortfolioCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePortfolioItemRequest extends FormRequest
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
            'title' => ['required_without:items', 'nullable', 'string', 'max:255'],
            'category' => ['required_without:items', 'nullable', Rule::exists(PortfolioCategory::class, 'slug')->where('is_active', true)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'images' => ['nullable', 'array', 'list', 'max:20'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'display_order' => ['required_without:items', 'nullable', 'integer', 'min:0', 'max:10000'],
            'is_featured' => ['sometimes', 'boolean'],
            'items' => ['sometimes', 'array', 'list', 'min:1', 'max:20'],
            'items.*' => ['array:title,category,alt_text,display_order,is_featured'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.category' => ['required', Rule::exists(PortfolioCategory::class, 'slug')->where('is_active', true)],
            'items.*.alt_text' => ['nullable', 'string', 'max:255'],
            'items.*.display_order' => ['required', 'integer', 'min:0', 'max:10000'],
            'items.*.is_featured' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['category.exists' => 'Vui lòng chọn danh mục đang hoạt động.', 'images.required' => 'Vui lòng chọn ít nhất một hình ảnh.', 'image.image' => 'Tệp tải lên phải là hình ảnh.', 'image.mimes' => 'Chỉ hỗ trợ ảnh JPG, PNG hoặc WebP.', 'image.max' => 'Ảnh tối đa 8 MB.', 'images.max' => 'Mỗi lần chỉ được tải tối đa 20 ảnh.', 'images.*.image' => 'Tệp tải lên phải là hình ảnh.', 'images.*.mimes' => 'Chỉ hỗ trợ ảnh JPG, PNG hoặc WebP.', 'images.*.max' => 'Mỗi ảnh tối đa 8 MB.'];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->hasFile('image') && ! $this->hasFile('images')) {
                $validator->errors()->add('images', 'Vui lòng chọn ít nhất một hình ảnh.');
            }
            if ($this->has('items') && is_array($this->input('items'))) {
                $uploads = $this->file('images', []);
                if (! is_array($uploads) || count($uploads) !== count($this->input('items'))) {
                    $validator->errors()->add('items', 'Thông tin từng ảnh không khớp với số ảnh đã chọn. Vui lòng chọn lại ảnh.');
                }
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'items.*.title' => 'tên tác phẩm của ảnh :position',
            'items.*.category' => 'danh mục của ảnh :position',
            'items.*.display_order' => 'thứ tự của ảnh :position',
            'items.*.alt_text' => 'mô tả của ảnh :position',
            'items.*.is_featured' => 'trạng thái hiển thị của ảnh :position',
        ];
    }
}

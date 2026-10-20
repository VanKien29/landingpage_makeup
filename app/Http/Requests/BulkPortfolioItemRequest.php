<?php

namespace App\Http\Requests;

use App\Models\PortfolioItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkPortfolioItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-academy') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['delete', 'show', 'hide'])],
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'integer', 'distinct', Rule::exists(PortfolioItem::class, 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'action.in' => 'Thao tác không hợp lệ.',
            'ids.required' => 'Vui lòng chọn ít nhất một tác phẩm.',
            'ids.max' => 'Chỉ xử lý tối đa 100 tác phẩm mỗi lần.',
            'ids.*.exists' => 'Một tác phẩm đã bị xóa. Vui lòng tải lại danh sách.',
            'ids.*.distinct' => 'Danh sách tác phẩm bị trùng lặp.',
        ];
    }
}

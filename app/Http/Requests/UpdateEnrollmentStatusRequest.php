<?php

namespace App\Http\Requests;

use App\Models\Enrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnrollmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-academy') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_keys(Enrollment::StatusLabels))],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái tư vấn.',
            'status.in' => 'Trạng thái tư vấn không hợp lệ.',
            'admin_notes.max' => 'Ghi chú nội bộ tối đa 2.000 ký tự.',
        ];
    }
}

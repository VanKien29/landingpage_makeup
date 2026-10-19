<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkshopRegistrationRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^[+0-9() .-]{8,40}$/'],
            'email' => ['required', 'email', 'max:255'],
            'social' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:40'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'agree' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return ['required' => 'Vui lòng nhập :attribute.', 'email.email' => 'Vui lòng nhập email hợp lệ.', 'phone.regex' => 'Vui lòng nhập số điện thoại hợp lệ.', 'quantity.integer' => 'Số lượng vé phải là số nguyên.', 'quantity.min' => 'Vui lòng chọn ít nhất 01 vé.', 'quantity.max' => 'Mỗi đăng ký không được vượt quá 40 vé.', 'agree.accepted' => 'Vui lòng đồng ý với chính sách đăng ký và xử lý dữ liệu cá nhân.', 'max.string' => ':attribute vượt quá độ dài cho phép.'];
    }

    public function attributes(): array
    {
        return ['name' => 'họ và tên', 'phone' => 'số điện thoại', 'email' => 'email', 'social' => 'Facebook hoặc Instagram', 'quantity' => 'số lượng vé', 'notes' => 'ghi chú'];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\WorkshopRegistration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkshopRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
        if (is_string($this->input('phone'))) {
            $this->merge(['phone' => preg_replace('/[\s().-]/u', '', $this->input('phone'))]);
        }
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
            'phone' => ['required', 'string', 'max:40', 'regex:/^(?:0[1-9][0-9]{8,9}|\+81[1-9][0-9]{8,9})$/'],
            'email' => ['required', 'email', 'max:255'],
            'social' => ['required', 'string', 'max:255'],
            'experience' => ['required', Rule::in(array_keys(WorkshopRegistration::ExperienceLabels))],
            'attendance' => ['accepted'],
            'agree' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return ['required' => 'Vui lòng nhập :attribute.', 'email.email' => 'Vui lòng nhập email hợp lệ.', 'phone.regex' => 'Vui lòng nhập số điện thoại tại Nhật Bản (bắt đầu bằng 0 hoặc +81).', 'experience.in' => 'Vui lòng chọn tình trạng makeup phù hợp.', 'attendance.accepted' => 'Vui lòng xác nhận có thể tham dự workshop và chờ ban tổ chức liên hệ khi chốt ngày.', 'agree.accepted' => 'Vui lòng đồng ý với chính sách đăng ký và xử lý dữ liệu cá nhân.', 'max.string' => ':attribute vượt quá độ dài cho phép.'];
    }

    public function attributes(): array
    {
        return ['name' => 'họ và tên', 'phone' => 'số điện thoại tại Nhật Bản', 'email' => 'email', 'social' => 'Facebook hoặc Instagram cá nhân', 'experience' => 'tình trạng hiện tại'];
    }
}

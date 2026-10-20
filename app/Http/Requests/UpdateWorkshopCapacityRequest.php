<?php

namespace App\Http\Requests;

use App\Models\WorkshopEvent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateWorkshopCapacityRequest extends FormRequest
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
        return ['capacity' => ['required', 'integer', 'min:1', 'max:60']];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('capacity')) {
                return;
            }

            $workshop = $this->route('workshop');
            if ($workshop instanceof WorkshopEvent && $this->integer('capacity') < $workshop->confirmed_tickets) {
                $validator->errors()->add('capacity', 'Sức chứa không được nhỏ hơn số học viên đã xác nhận.');
            }
        }];
    }
}

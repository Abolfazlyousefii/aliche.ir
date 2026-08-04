<?php

namespace App\Http\Requests\Admin;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mobile' => PhoneNumber::normalize($this->input('mobile')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($userId)],
            'mobile' => ['required', 'string', 'max:30', Rule::unique('users', 'mobile')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'union_id' => ['nullable', 'integer', 'exists:unions,id'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'نام',
            'email' => 'ایمیل',
            'mobile' => 'شماره تماس',
            'password' => 'رمز عبور',
            'roles' => 'نقش‌ها',
            'union_id' => 'اتحادیه مرتبط',
            'is_active' => 'وضعیت',
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Models\UnionMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnionMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'union_id' => $this->unionRule(),
            'full_name' => ['required', 'string', 'max:190'],
            'national_code' => ['nullable', 'string', 'max:20'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'membership_code' => ['nullable', 'string', 'max:100'],
            'business_name' => ['nullable', 'string', 'max:190'],
            'business_license_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', Rule::in(UnionMember::STATUSES)],
            'description' => ['nullable', 'string', 'max:2000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /** @return array<int, mixed> */
    private function unionRule(): array
    {
        $rule = ['required', 'exists:unions,id'];

        if (! $this->user()?->hasRole('super-admin')) {
            $rule[] = Rule::in([(int) $this->user()?->union_id]);
        }

        return $rule;
    }
}

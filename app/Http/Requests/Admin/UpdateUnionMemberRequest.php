<?php

namespace App\Http\Requests\Admin;

use App\Models\UnionMember;
use App\Rules\SafeImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnionMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->route('union_member');

        if (! $member instanceof UnionMember) {
            return true;
        }

        return $this->user()?->hasRole('super-admin') || (int) $member->union_id === (int) $this->user()?->union_id;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('first_name') || $this->filled('last_name')) {
            $this->merge(['full_name' => trim((string) $this->input('first_name').' '.(string) $this->input('last_name'))]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'union_id' => $this->unionRule(),
            'full_name' => ['required', 'string', 'max:190'],
            'first_name' => ['nullable', 'required_with:last_name', 'string', 'max:90'],
            'last_name' => ['nullable', 'required_with:first_name', 'string', 'max:90'],
            'position' => ['nullable', 'string', 'max:190'],
            'image' => ['nullable', 'bail', 'file', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'remove_image' => ['nullable', 'boolean'],
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
            'delete_attachments' => ['nullable', 'array'],
            'delete_attachments.*' => ['string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
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

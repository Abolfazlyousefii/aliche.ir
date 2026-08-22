<?php

namespace App\Http\Requests\Admin;

use App\Models\GuildUnion;
use App\Rules\SafeImageUpload;
use App\Services\SlugService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->input('slug');
        if ($slug !== null && ! filter_var($slug, FILTER_VALIDATE_URL)) {
            $this->merge(['slug' => app(SlugService::class)->make((string) $slug, '')]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $union = $this->route('union');
        $unionId = $union instanceof GuildUnion ? $union->id : null;

        return [
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'regex:/^[\p{Arabic}A-Za-z0-9\-]+$/u', Rule::unique('unions', 'slug')->ignore($unionId)],
            'logo' => ['nullable', 'bail', 'file', 'image', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'cover_image' => ['nullable', 'bail', 'file', 'image', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:190'],
            'website' => ['nullable', 'url', 'max:190'],
            'manager_name' => ['nullable', 'string', 'max:190'],
            'manager_position' => ['nullable', 'string', 'max:190'],
            'manager_description' => ['nullable', 'string', 'max:3000'],
            'union_type' => ['nullable', 'string', 'max:190'],
            'union_type_id' => ['nullable', 'exists:union_types,id'],
            'manager_image' => ['nullable', 'bail', 'file', 'image', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'working_hours' => ['nullable', 'string', 'max:500'],
            'social_links' => ['nullable', 'array'],
            'social_links.*' => ['nullable', 'url', 'max:190'],
            'settings' => ['nullable', 'array'],
            'settings.*' => ['nullable', 'boolean'],
            'news_mode' => ['required', Rule::in(['auto', 'manual', 'disabled'])],
            'president_buttons' => ['nullable', 'array'],
            'president_buttons.*.title' => ['nullable', 'string', 'max:190'],
            'president_buttons.*.url' => ['nullable', 'string', 'max:500'],
            'president_buttons.*.icon' => ['nullable', 'string', 'max:50'],
            'president_buttons.*.target' => ['nullable', Rule::in(['_self', '_blank'])],
            'president_buttons.*.is_active' => ['nullable', 'boolean'],
            'selected_posts' => ['nullable', 'array'],
            'selected_posts.*' => ['nullable', 'integer', 'exists:posts,id'],
            'price_list_mode' => ['required', Rule::in(['image', 'table'])],
            'price_list_image' => ['nullable', 'bail', 'file', 'image', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'complaint_enabled' => ['required', 'boolean'],
            'congratulations_enabled' => ['required', 'boolean'],
            'news_enabled' => ['required', 'boolean'],
            'announcements_enabled' => ['required', 'boolean'],
            'gallery_enabled' => ['required', 'boolean'],
            'videos_enabled' => ['required', 'boolean'],
            'members_enabled' => ['required', 'boolean'],
            'services_enabled' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],

            'related' => ['nullable', 'array'],
            'related.commissions' => ['nullable', 'array'],
            'related.commissions.*.id' => ['nullable', 'integer', 'exists:union_commissions,id'],
            'related.commissions.*.title' => ['nullable', 'string', 'max:190'],
            'related.commissions.*.description' => ['nullable', 'string'],
            'related.commissions.*.icon' => ['nullable', 'string', 'max:50'],
            'related.commissions.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'related.commissions.*.is_active' => ['nullable', 'boolean'],
            'related.commissions.*.delete' => ['nullable', 'boolean'],
            'related.commissions.*.tasks' => ['nullable', 'array'],
            'related.commissions.*.tasks.*.id' => ['nullable', 'integer', 'exists:union_commission_tasks,id'],
            'related.commissions.*.tasks.*.title' => ['nullable', 'string', 'max:190'],
            'related.commissions.*.tasks.*.description' => ['nullable', 'string'],
            'related.commissions.*.tasks.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'related.commissions.*.tasks.*.is_active' => ['nullable', 'boolean'],
            'related.commissions.*.tasks.*.delete' => ['nullable', 'boolean'],
            'related.rules' => ['nullable', 'array'],
            'related.rules.*.id' => ['nullable', 'integer', 'exists:union_rules,id'],
            'related.rules.*.title' => ['nullable', 'string', 'max:190'],
            'related.rules.*.description' => ['nullable', 'string'],
            'related.rules.*.icon' => ['nullable', 'string', 'max:50'],
            'related.rules.*.file' => ['nullable', 'string', 'max:500'],
            'related.rules.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'related.rules.*.is_active' => ['nullable', 'boolean'],
            'related.rules.*.delete' => ['nullable', 'boolean'],
            'related.minutes' => ['nullable', 'array'],
            'related.minutes.*.id' => ['nullable', 'integer', 'exists:union_minutes,id'],
            'related.minutes.*.title' => ['nullable', 'string', 'max:190'],
            'related.minutes.*.meeting_date' => ['nullable', 'string', 'max:20'],
            'related.minutes.*.file' => ['nullable', 'string', 'max:500'],
            'related.minutes.*.description' => ['nullable', 'string'],
            'related.minutes.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'related.minutes.*.is_active' => ['nullable', 'boolean'],
            'related.minutes.*.delete' => ['nullable', 'boolean'],
            'related.educations' => ['nullable', 'array'],
            'related.educations.*.id' => ['nullable', 'integer', 'exists:union_educations,id'],
            'related.educations.*.title' => ['nullable', 'string', 'max:190'],
            'related.educations.*.description' => ['nullable', 'string'],
            'related.educations.*.icon' => ['nullable', 'string', 'max:50'],
            'related.educations.*.link' => ['nullable', 'string', 'max:500'],
            'related.educations.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'related.educations.*.is_active' => ['nullable', 'boolean'],
            'related.educations.*.delete' => ['nullable', 'boolean'],
            'related.prices' => ['nullable', 'array'],
            'related.prices.*.id' => ['nullable', 'integer', 'exists:union_prices,id'],
            'related.prices.*.title' => ['nullable', 'string', 'max:190'],
            'related.prices.*.price' => ['nullable', 'numeric', 'min:0'],
            'related.prices.*.currency' => ['nullable', 'string', 'max:50'],
            'related.prices.*.type' => ['nullable', 'string', 'max:100'],
            'related.prices.*.updated_on' => ['nullable', 'string', 'max:20'],
            'related.prices.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'related.prices.*.is_active' => ['nullable', 'boolean'],
            'related.prices.*.delete' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        $messages = [
            'slug.regex' => 'اسلاگ فقط می‌تواند شامل حروف فارسی یا انگلیسی، عدد و خط تیره باشد.',
            'slug.max' => 'اسلاگ نمی‌تواند بیشتر از ۱۹۰ کاراکتر باشد.',
            'logo.max' => 'حجم لوگوی اتحادیه نباید بیشتر از ۵ مگابایت باشد.',
            'cover_image.max' => 'حجم تصویر اصلی اتحادیه نباید بیشتر از ۵ مگابایت باشد.',
            'manager_image.max' => 'حجم تصویر رئیس اتحادیه نباید بیشتر از ۵ مگابایت باشد.',
            'price_list_image.max' => 'حجم تصویر نرخنامه نباید بیشتر از ۵ مگابایت باشد.',
        ];
        foreach (['logo', 'cover_image', 'manager_image', 'price_list_image'] as $field) {
            $messages[$field.'.image'] = 'فایل انتخاب‌شده باید یک تصویر معتبر باشد.';
        }

        return $messages;
    }

    public function attributes(): array
    {
        return ['title' => 'عنوان اتحادیه', 'slug' => 'اسلاگ', 'logo' => 'لوگوی اتحادیه', 'cover_image' => 'تصویر اصلی اتحادیه', 'manager_image' => 'تصویر رئیس اتحادیه', 'price_list_image' => 'تصویر نرخنامه'];
    }
}

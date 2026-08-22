<?php

namespace App\Http\Requests\Admin;

use App\Models\Announcement;
use App\Rules\SafeImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $announcement = $this->route('announcement');
        $announcementId = $announcement instanceof Announcement ? $announcement->id : null;

        return [
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'regex:/^[\p{Arabic}\p{L}\p{N}\-]+$/u', Rule::unique('announcements', 'slug')->ignore($announcementId)],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'bail', 'file', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'featured_image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'attachment' => ['nullable', 'file', 'max:10240'],
            'category_id' => ['nullable', 'exists:announcement_categories,id'],
            'union_id' => ['nullable', 'exists:unions,id'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', ...($this->filled('starts_at') ? ['after_or_equal:starts_at'] : [])],
            'is_important' => ['required', 'boolean'],
            'show_on_home' => ['required', 'boolean'],
            'status' => ['required', 'string', Rule::in($this->allowedStatuses())],
            'visibility' => ['required', 'string', Rule::in(['public', 'private'])],
            'published_at' => ['nullable', 'date'],
            'rejected_reason' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'remove_attachment' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'starts_at' => jalali_to_gregorian_datetime($this->input('starts_at')),
            'expires_at' => jalali_to_gregorian_datetime($this->input('expires_at'), true),
            'published_at' => jalali_to_gregorian_datetime($this->input('published_at')),
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'slug.unique' => 'این نامک قبلاً برای اطلاعیه دیگری ثبت شده است. لطفاً نامک دیگری وارد کنید یا این فیلد را خالی بگذارید تا سیستم نامک یکتا بسازد.',
            'slug.regex' => 'نامک فقط می‌تواند شامل حروف فارسی/لاتین، عدد و خط تیره باشد.',
            'category_id.exists' => 'دسته‌بندی انتخاب‌شده معتبر نیست.',
            'featured_image_media_id.exists' => 'تصویر انتخاب‌شده از کتابخانه رسانه معتبر نیست.',
            'featured_image.image' => 'فایل تصویر شاخص باید یک تصویر معتبر باشد.',
            'featured_image.max' => 'حجم تصویر شاخص نباید بیشتر از ۴ مگابایت باشد.',
            'attachment.max' => 'حجم فایل پیوست نباید بیشتر از ۱۰ مگابایت باشد.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'title' => 'عنوان اطلاعیه',
            'slug' => 'نامک',
            'excerpt' => 'خلاصه اطلاعیه',
            'body' => 'متن کامل اطلاعیه',
            'featured_image' => 'تصویر شاخص',
            'featured_image_media_id' => 'تصویر انتخاب‌شده از کتابخانه',
            'attachment' => 'فایل پیوست',
            'category_id' => 'دسته‌بندی',
            'union_id' => 'اتحادیه مرتبط',
            'starts_at' => 'شروع نمایش',
            'expires_at' => 'تاریخ انقضا',
            'published_at' => 'تاریخ انتشار',
            'is_important' => 'اطلاعیه مهم',
            'show_on_home' => 'نمایش در صفحه اصلی',
            'status' => 'وضعیت',
            'visibility' => 'نوع نمایش',
            'rejected_reason' => 'دلیل رد اطلاعیه',
            'sort_order' => 'ترتیب نمایش',
            'is_active' => 'فعال بودن',
        ];
    }

    /** @return array<int, string> */
    private function allowedStatuses(): array
    {
        $statuses = Announcement::LIMITED_STATUSES;

        if ($this->user()?->hasPermission('announcements.approve')) {
            $statuses = array_merge($statuses, ['approved', 'rejected', 'archived']);
        }

        if ($this->user()?->hasPermission('announcements.publish')) {
            $statuses[] = 'published';
        }

        return array_values(array_unique($statuses));
    }
}

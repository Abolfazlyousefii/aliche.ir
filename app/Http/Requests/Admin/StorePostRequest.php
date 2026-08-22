<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use App\Services\SlugService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->normalizedSlug(),
            'meta_keywords' => $this->normalizeMetaKeywords($this->input('meta_keywords')),
            'is_important' => $this->boolean('is_important'),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'regex:/^[\p{Arabic}A-Za-z0-9\-]+$/u'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'featured_media_id' => ['nullable', 'exists:media,id'],
            'gallery_media_ids' => ['nullable', 'array'],
            'gallery_media_ids.*' => ['integer', 'exists:media,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'union_id' => ['nullable', 'exists:unions,id'],
            'type' => ['required', 'string', Rule::in(Post::TYPES)],
            'homepage_position' => ['required', 'string', Rule::in(Post::HOMEPAGE_POSITIONS)],
            'is_important' => ['required', 'boolean'],
            'featured_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', Rule::in($this->allowedStatuses())],
            'published_at' => ['nullable', 'date'],
            'rejected_reason' => ['nullable', 'string', 'max:1000'],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'array'],
            'meta_keywords.*' => ['nullable', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'max:4096'],
            'gallery_captions' => ['nullable', 'array'],
            'gallery_captions.*' => ['nullable', 'string', 'max:190'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'اسلاگ فقط می‌تواند شامل حروف فارسی یا انگلیسی، عدد و خط تیره باشد.',
            'slug.max' => 'اسلاگ نمی‌تواند بیشتر از ۱۹۰ کاراکتر باشد.',
            'title.required' => 'وارد کردن عنوان الزامی است.',
            'title.max' => 'عنوان نمی‌تواند بیشتر از ۱۹۰ کاراکتر باشد.',
            'featured_image.image' => 'فایل تصویر شاخص باید یک تصویر معتبر باشد.',
            'featured_image.max' => 'حجم تصویر شاخص نباید بیشتر از ۴ مگابایت باشد.',
            'type.in' => 'نوع محتوای انتخاب‌شده معتبر نیست.',
        ];
    }

    public function attributes(): array
    {
        return ['title' => 'عنوان', 'slug' => 'اسلاگ', 'featured_image' => 'تصویر شاخص', 'type' => 'نوع محتوا'];
    }

    private function normalizedSlug(): ?string
    {
        $slug = $this->input('slug');
        if ($slug === null || filter_var($slug, FILTER_VALIDATE_URL)) {
            return $slug;
        }

        return app(SlugService::class)->make((string) $slug, '');
    }

    /** @return array<int, string> */
    private function allowedStatuses(): array
    {
        $statuses = Post::LIMITED_STATUSES;

        if ($this->user()?->hasPermission('posts.approve')) {
            $statuses = array_merge($statuses, ['approved', 'rejected', 'archived']);
        }

        if ($this->user()?->hasPermission('posts.publish')) {
            $statuses[] = 'published';
        }

        return array_values(array_unique($statuses));
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private function normalizeMetaKeywords(mixed $value): array
    {
        $items = is_array($value) ? $value : preg_split('/[,\n،]+/u', (string) $value);

        return collect($items)
            ->map(fn ($keyword) => trim((string) $keyword))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}

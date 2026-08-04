<?php

namespace App\Http\Requests\Admin;

use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $pageId = $this->route('page')?->id;

        return [
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'regex:/^[\p{Arabic}\p{L}\p{N}\-]+$/u', Rule::unique('pages', 'slug')->ignore($pageId)],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
            'featured_image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'template' => ['required', 'string', Rule::in(Page::TEMPLATES)],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', Rule::in($this->allowedStatuses())],
            'published_at' => ['nullable', 'date'],
            'rejected_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /** @return array<int, string> */
    private function allowedStatuses(): array
    {
        return app(\App\Services\ContentApprovalService::class)->allowedStatusesFor($this->user(), ['pages.approve', 'pages.publish']);
    }
}

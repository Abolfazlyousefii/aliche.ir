<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\System;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_shows_only_active_published_systems_in_order(): void
    {
        $this->system('سامانه دوم', 'second-system', ['sort_order' => 20]);
        $this->system('سامانه نخست', 'first-system', ['sort_order' => 10]);
        $this->system('سامانه غیرفعال', 'inactive-system', ['is_active' => false]);
        $this->system('سامانه پیش‌نویس', 'draft-system', ['status' => 'draft']);
        $this->system('سامانه آینده', 'future-system', ['published_at' => now()->addDay()]);

        $response = $this->get(route('systems.index'));

        $response->assertOk()
            ->assertViewIs('frontend.systems.index')
            ->assertSee('systems-directory-page', false)
            ->assertSeeInOrder(['سامانه نخست', 'سامانه دوم'])
            ->assertDontSee('سامانه غیرفعال')
            ->assertDontSee('سامانه پیش‌نویس')
            ->assertDontSee('سامانه آینده')
            ->assertViewHas('systems', fn ($systems) => $systems->total() === 2);
    }

    public function test_search_matches_title_description_and_valid_category(): void
    {
        $category = $this->category('سامانه‌های آموزشی', 'system-education');
        $this->system('درگاه آموزش اصناف', 'education-system', [
            'category_id' => $category->id,
            'short_description' => 'دوره‌های مهارتی ویژه کسب‌وکار',
        ]);
        $this->system('درگاه مجوز', 'license-system');

        foreach (['آموزش', 'مهارتی', 'سامانه‌های آموزشی'] as $search) {
            $this->get(route('systems.index', ['search' => $search]))
                ->assertOk()
                ->assertSee('درگاه آموزش اصناف')
                ->assertDontSee('درگاه مجوز')
                ->assertViewHas('systems', fn ($systems) => $systems->total() === 1);
        }

        $this->get(route('systems.index', ['search' => str_repeat('الف', 500)]))
            ->assertOk()
            ->assertSee('سامانه‌ای پیدا نشد');
    }

    public function test_only_attached_system_categories_are_filterable_and_video_category_is_hidden(): void
    {
        $education = $this->category('آموزشی', 'system-education');
        $licenses = $this->category('مجوزها', 'system-license');
        $unused = $this->category('بدون سامانه', 'system-unused');
        $video = $this->category('ویدیوهای عمومی', 'video-general', ['type' => 'video']);
        $this->system('سامانه آموزش', 'education', ['category_id' => $education->id]);
        $this->system('سامانه مجوز', 'license', ['category_id' => $licenses->id]);
        $this->system('سامانه با دسته اشتباه', 'wrong-category', ['category_id' => $video->id]);

        $response = $this->get(route('systems.index'));
        $response->assertOk()
            ->assertSee('data-systems-category-tabs', false)
            ->assertSee('آموزشی')
            ->assertSee('مجوزها')
            ->assertDontSee('بدون سامانه')
            ->assertDontSee('ویدیوهای عمومی')
            ->assertViewHas('categories', fn ($categories) => $categories->pluck('id')->all() === [$education->id, $licenses->id]);

        $this->get(route('systems.index', ['category' => $education->slug]))
            ->assertOk()
            ->assertSee('سامانه آموزش')
            ->assertDontSee('سامانه مجوز')
            ->assertViewHas('activeCategory', $education->slug);

        $this->get(route('systems.index', ['category' => $unused->slug]))
            ->assertOk()
            ->assertViewHas('activeCategory', '')
            ->assertViewHas('systems', fn ($systems) => $systems->total() === 3);
    }

    public function test_single_valid_category_does_not_render_redundant_tabs(): void
    {
        $category = $this->category('سامانه‌های عمومی', 'system-general');
        $this->system('سامانه عمومی', 'general-system', ['category_id' => $category->id]);

        $this->get(route('systems.index'))
            ->assertOk()
            ->assertDontSee('data-systems-category-tabs', false)
            ->assertSee('سامانه‌های عمومی');
    }

    public function test_entry_link_validation_and_icon_fallback_are_safe(): void
    {
        $external = $this->system('سامانه خارجی', 'external-system', ['link' => 'https://service.test/path', 'icon' => '🌐']);
        $internal = $this->system('سامانه داخلی', 'internal-system', ['link' => '/complaints', 'icon' => '📨']);
        $this->system('سامانه بدون لینک', 'no-link', ['link' => null, 'icon' => null, 'short_description' => null, 'description' => null]);
        $this->system('سامانه لینک نامعتبر', 'invalid-link', ['link' => 'javascript:alert(1)', 'icon' => '💻']);
        $this->system('سامانه نمونه', 'placeholder-link', ['link' => 'https://example.com', 'icon' => '🎓']);

        $response = $this->get(route('systems.index'));
        $response->assertOk()
            ->assertSee('href="https://service.test/path"', false)
            ->assertSee('target="_blank" rel="noopener noreferrer"', false)
            ->assertSee('href="'.url('/complaints').'"', false)
            ->assertSee(route('systems.show', $external), false)
            ->assertSee(route('systems.show', $internal), false)
            ->assertDontSee('javascript:alert(1)', false)
            ->assertDontSee('https://example.com', false)
            ->assertDontSee('💻')
            ->assertDontSee('🌐')
            ->assertDontSee('📨')
            ->assertSee('system-directory-icon', false)
            ->assertDontSee('توضیحات این سامانه به‌زودی تکمیل می‌شود.');
    }

    public function test_ajax_pagination_returns_shared_results_and_preserves_filters(): void
    {
        $category = $this->category('سامانه‌های خدماتی', 'system-services');
        foreach (range(1, 13) as $index) {
            $this->system("سامانه خدمات {$index}", "service-system-{$index}", [
                'category_id' => $category->id,
                'short_description' => 'خدمت ویژه کاربران',
                'sort_order' => $index,
            ]);
        }
        $this->system('سامانه غیرفعال ایجکس', 'inactive-ajax-system', [
            'category_id' => $category->id,
            'is_active' => false,
        ]);

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->get(route('systems.index', [
            'category' => $category->slug,
            'search' => 'خدمت ویژه',
            'page' => 2,
        ]));

        $response->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 13)
            ->assertJsonPath('from', 13)
            ->assertJsonPath('to', 13)
            ->assertJsonPath('category_counts.all', 13)
            ->assertJsonPath('category_counts.system-services', 13)
            ->assertJsonStructure(['html', 'current_page', 'last_page', 'total', 'from', 'to', 'category_counts', 'url']);

        $html = $response->json('html');
        $this->assertStringContainsString('data-systems-results', $html);
        $this->assertStringContainsString('systems-directory-pagination', $html);
        $this->assertStringContainsString('category=system-services', $html);
        $this->assertStringContainsString('search=%D8%AE%D8%AF%D9%85%D8%AA%20%D9%88%DB%8C%DA%98%D9%87', $html);
        $this->assertStringNotContainsString('سامانه غیرفعال ایجکس', $html);
    }

    public function test_empty_directory_uses_professional_empty_state_without_pagination(): void
    {
        $this->get(route('systems.index'))
            ->assertOk()
            ->assertSee('سامانه‌ای پیدا نشد')
            ->assertSee('نمایش همه سامانه‌ها')
            ->assertDontSee('systems-directory-pagination', false);
    }

    private function system(string $title, string $slug, array $attributes = []): System
    {
        return System::query()->create(array_merge([
            'title' => $title,
            'slug' => $slug,
            'short_description' => 'توضیح کوتاه سامانه برای آزمون دایرکتوری.',
            'link' => null,
            'target' => '_self',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 0,
            'is_active' => true,
        ], $attributes));
    }

    private function category(string $title, string $slug, array $attributes = []): Category
    {
        return Category::query()->create(array_merge([
            'title' => $title,
            'slug' => $slug,
            'type' => 'system',
            'sort_order' => 0,
            'is_active' => true,
        ], $attributes));
    }
}

<?php

namespace Tests\Feature\Frontend;

use App\Models\Category;
use App\Models\TourismPlace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomeTourismCategoriesTest extends TestCase
{
    use RefreshDatabase;

    private function category(string $slug, array $data = []): Category
    {
        $category = Category::create(array_replace(['title' => $slug, 'slug' => $slug, 'type' => 'tourism', 'is_active' => true], $data));
        // Fresh installations still have the legacy post_categories FK.
        // Supply its fixture too; keep constraints enabled during these tests.
        DB::table('post_categories')->insert(['id' => $category->id, 'title' => $slug, 'slug' => $slug]);
        return $category;
    }

    private function place(string $slug, array $data = []): TourismPlace
    {
        return TourismPlace::create(array_replace(['title' => $slug, 'slug' => $slug, 'status' => 'published', 'published_at' => now()->subMinute(), 'is_active' => true], $data));
    }

    public function test_legacy_nature_records_join_their_category_even_when_other_categories_have_content(): void
    {
        $nature = $this->category('tourism-nature', ['title' => 'طبیعت‌گردی']);
        $shopping = $this->category('tourism-shopping');
        $this->place('جنگل ناهارخوران', ['slug' => 'nahar-test', 'tourism_type' => 'nature', 'category_id' => null]);
        $this->place('بازار تست', ['slug' => 'market-test', 'category_id' => $shopping->id, 'tourism_type' => 'shopping']);
        $this->get(route('home'))->assertOk()->assertViewHas('tourismPanels', function ($panels) use ($nature, $shopping) {
            return $panels->has('tourism-category-'.$nature->id)
                && $panels->get('tourism-category-'.$nature->id)['items']->pluck('slug')->all() === ['nahar-test']
                && $panels->get('tourism-category-'.$shopping->id)['items']->pluck('slug')->all() === ['market-test'];
        })->assertSee('جنگل ناهارخوران')->assertSee('طبیعت‌گردی');
    }

    public function test_all_nonempty_custom_categories_appear_and_unpublished_or_empty_categories_do_not(): void
    {
        $this->category('empty-category');
        foreach (range(1, 6) as $i) {
            $category = $this->category('custom-'.$i);
            $this->place('place-'.$i, ['category_id' => $category->id]);
        }
        $draft = $this->category('draft-only');
        $this->place('draft-place', ['category_id' => $draft->id, 'status' => 'draft']);
        $this->get(route('home'))->assertOk()->assertViewHas('tourismPanels', fn ($panels) => $panels->count() === 6)
            ->assertDontSee('empty-category')->assertDontSee('draft-only')->assertDontSee('draft-place');
    }

    public function test_type_fallback_does_not_reassign_explicit_categories_or_expose_hidden_content(): void
    {
        $nature = $this->category('tourism-nature');
        $custom = $this->category('custom-nature');
        $inactive = $this->category('inactive', ['is_active' => false]);
        $this->place('explicit-custom', ['category_id' => $custom->id, 'tourism_type' => 'nature']);
        $this->place('explicit-inactive', ['category_id' => $inactive->id, 'tourism_type' => 'nature']);
        $this->place('nature-draft', ['tourism_type' => 'nature', 'status' => 'draft']);
        $this->place('nature-future', ['tourism_type' => 'nature', 'published_at' => now()->addDay()]);
        $this->place('nature-inactive', ['tourism_type' => 'nature', 'is_active' => false]);
        $this->get(route('home'))->assertOk()->assertViewHas('tourismPanels', fn ($panels) => !$panels->has('tourism-category-'.$nature->id))
            ->assertSee('explicit-custom')->assertDontSee('explicit-inactive')->assertDontSee('nature-draft')->assertDontSee('nature-future')->assertDontSee('nature-inactive');
    }
}

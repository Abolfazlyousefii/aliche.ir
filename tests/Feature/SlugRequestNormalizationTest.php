<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\StoreUnionRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Http\Requests\Admin\UpdateUnionRequest;
use Illuminate\Support\Facades\Validator;
use ReflectionMethod;
use Tests\TestCase;

class SlugRequestNormalizationTest extends TestCase
{
    public function test_post_and_union_requests_normalize_slug_before_validation(): void
    {
        foreach ([StorePostRequest::class, UpdatePostRequest::class, StoreUnionRequest::class, UpdateUnionRequest::class] as $requestClass) {
            $request = $requestClass::create('/', 'POST', ['slug' => 'Gold Market_News--1405']);
            $request->setContainer($this->app);
            (new ReflectionMethod($requestClass, 'prepareForValidation'))->invoke($request);

            $this->assertSame('gold-market-news-1405', $request->input('slug'));
        }
    }

    public function test_full_url_is_rejected_with_a_persian_slug_message(): void
    {
        $request = StorePostRequest::create('/', 'POST', ['slug' => 'https://gorganasnaf.ir/news/my-post']);
        $request->setContainer($this->app);
        (new ReflectionMethod(StorePostRequest::class, 'prepareForValidation'))->invoke($request);

        $rules = (new StorePostRequest())->rules()['slug'];
        $validator = Validator::make(['slug' => $request->input('slug')], ['slug' => $rules], $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertSame('اسلاگ فقط می‌تواند شامل حروف فارسی یا انگلیسی، عدد و خط تیره باشد.', $validator->errors()->first('slug'));
    }
}

<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\StoreUnionRequest;
use App\Http\Requests\Admin\UpdateUnionRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UnionRequestValidationTest extends TestCase
{
    public function test_union_image_rules_are_consistently_five_megabytes_and_category_is_not_submitted(): void
    {
        foreach ([new StoreUnionRequest(), new UpdateUnionRequest()] as $request) {
            $rules = $request->rules();
            $this->assertArrayNotHasKey('category_id', $rules);

            foreach (['logo', 'cover_image', 'manager_image', 'price_list_image'] as $field) {
                $this->assertContains('max:5120', $rules[$field]);
                $this->assertContains('image', $rules[$field]);

                $valid = Validator::make([$field => UploadedFile::fake()->image($field.'.jpg')->size(5000)], [$field => $rules[$field]], $request->messages());
                $this->assertFalse($valid->fails());

                $tooLarge = Validator::make([$field => UploadedFile::fake()->image($field.'.jpg')->size(5121)], [$field => $rules[$field]], $request->messages());
                $this->assertTrue($tooLarge->fails());
                $this->assertStringNotContainsString('validation.max.file', $tooLarge->errors()->first($field));

                $notImage = Validator::make([$field => UploadedFile::fake()->create($field.'.txt', 10, 'text/plain')], [$field => $rules[$field]], $request->messages());
                $this->assertTrue($notImage->fails());
                $this->assertStringNotContainsString('validation.image', $notImage->errors()->first($field));
            }
        }
    }
}

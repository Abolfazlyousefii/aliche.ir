<?php

namespace Tests\Feature\Media;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RichTextUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->signInAsSuperAdmin();
    }

    public function test_spoofed_php_upload_is_rejected_or_saved_with_a_safe_detected_extension(): void
    {
        $response = $this->postJson(route('admin.rich_text.upload'), [
            'file' => UploadedFile::fake()->createWithContent('notes.php', str_repeat("Plain text UTF-8 document for MIME detection.\n", 100)),
            'type' => 'file',
        ]);

        // Fileinfo can classify a PHP-named fake as executable or generic
        // octet-stream. Both fail-closed rejection and safe text conversion
        // are valid; neither path may ever save a PHP file.
        if ($response->status() === 422) {
            $response->assertJsonValidationErrors('file');
            $this->assertSame([], Storage::disk('public')->allFiles('rich-text/files'));

            return;
        }

        $response->assertOk();
        $path = (string) $response->json('path');
        $this->assertStringEndsWith('.txt', $path);
        $this->assertStringNotContainsString('.php', $path);
        Storage::disk('public')->assertExists($path);
    }
}

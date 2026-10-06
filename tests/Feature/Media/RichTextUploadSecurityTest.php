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

    public function test_file_upload_uses_server_detected_extension_instead_of_client_extension(): void
    {
        $response = $this->postJson(route('admin.rich_text.upload'), [
            'file' => UploadedFile::fake()->createWithContent('notes.php', str_repeat("Plain text UTF-8 document for MIME detection.\n", 100)),
            'type' => 'file',
        ])->assertOk();

        $path = (string) $response->json('path');

        $this->assertStringEndsWith('.txt', $path);
        $this->assertStringNotContainsString('.php', $path);
        Storage::disk('public')->assertExists($path);
    }
}

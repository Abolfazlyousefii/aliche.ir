<?php

namespace App\Http\Controllers;

use App\Services\RichTextSanitizer;

abstract class Controller
{
    /**
     * @param array<string, mixed> $data
     * @param array<int, string> $fields
     * @return array<string, mixed>
     */
    protected function sanitizeRichTextFields(array $data, array $fields): array
    {
        $sanitizer = app(RichTextSanitizer::class);

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $sanitizer->sanitize($data[$field] ?? null);
            }
        }

        return $data;
    }

    protected function sanitizeRichText(?string $html): ?string
    {
        return app(RichTextSanitizer::class)->sanitize($html);
    }
}

<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class RichTextSanitizer
{
    /** @var array<int, string> */
    private array $allowedTags = [
        'a', 'b', 'blockquote', 'br', 'code', 'div', 'em', 'figcaption', 'figure', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'hr', 'i', 'img', 'li', 'ol', 'p', 'pre', 's', 'span', 'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'tfoot',
        'th', 'thead', 'tr', 'u', 'ul',
    ];

    /** @var array<int, string> */
    private array $discardWithContentTags = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button'];

    /** @var array<string, array<int, string>> */
    private array $allowedAttributes = [
        'a' => ['href', 'target', 'rel', 'title'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
    ];

    /** @var array<int, string> */
    private array $allowedGlobalAttributes = ['class'];

    public function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $html = trim(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($html === '') {
            return null;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $container = $document->documentElement;
        if (! $container instanceof DOMElement) {
            return null;
        }

        $this->sanitizeNode($container);

        $clean = '';
        foreach ($container->childNodes as $child) {
            $clean .= $document->saveHTML($child);
        }

        $clean = trim($clean);

        return $clean === '' ? null : $clean;
    }

    private function sanitizeNode(DOMNode $node): void
    {
        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $child = $node->childNodes->item($i);

            if (! $child) {
                continue;
            }

            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);

                if (! in_array($tag, $this->allowedTags, true)) {
                    if (in_array($tag, $this->discardWithContentTags, true)) {
                        $child->parentNode?->removeChild($child);
                    } else {
                        $this->unwrapNode($child);
                    }
                    continue;
                }

                $this->sanitizeAttributes($child, $tag);
            }

            $this->sanitizeNode($child);
        }
    }

    private function unwrapNode(DOMNode $node): void
    {
        $parent = $node->parentNode;

        if (! $parent) {
            return;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }

    private function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $allowed = array_merge($this->allowedGlobalAttributes, $this->allowedAttributes[$tag] ?? []);

        for ($i = $element->attributes->length - 1; $i >= 0; $i--) {
            $attribute = $element->attributes->item($i);

            if (! $attribute) {
                continue;
            }

            $name = strtolower($attribute->name);
            $value = trim($attribute->value);

            if (! in_array($name, $allowed, true) || str_starts_with($name, 'on')) {
                $element->removeAttribute($attribute->name);
                continue;
            }

            if (in_array($name, ['href', 'src'], true) && ! $this->hasSafeUrl($value, $name === 'src')) {
                $element->removeAttribute($attribute->name);
                continue;
            }

            if ($name === 'target' && ! in_array($value, ['_blank', '_self'], true)) {
                $element->removeAttribute($attribute->name);
                continue;
            }

            if ($name === 'target' && $value === '_blank') {
                $element->setAttribute('rel', 'noopener noreferrer');
            }
        }
    }

    private function hasSafeUrl(string $value, bool $isSource): bool
    {
        if ($value === '' || str_starts_with($value, '#') || str_starts_with($value, '/')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

        if (in_array($scheme, ['http', 'https'], true)) {
            return true;
        }

        return ! $isSource && in_array($scheme, ['mailto', 'tel'], true);
    }
}

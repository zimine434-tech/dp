<?php

namespace App\Support;

use Illuminate\Support\Str;

class RichTextPlain
{
    /**
     * HTML из WYSIWYG → обычный текст для карточек и списков.
     */
    public static function fromHtml(?string $html, int $limit = 0): string
    {
        $text = strip_tags((string) $html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $text = trim($text);

        if ($limit > 0 && $text !== '') {
            $text = Str::limit($text, $limit);
        }

        return $text;
    }

    public static function filled(?string $html): bool
    {
        return self::fromHtml($html) !== '';
    }
}

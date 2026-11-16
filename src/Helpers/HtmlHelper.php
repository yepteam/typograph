<?php

namespace Yepteam\Typograph\Helpers;

final class HtmlHelper
{

    /**
     * @var array|string[] Теги, которые, как правило, разделяют строки.
     */
    public static array $new_line_tags = [
        'p',
        'div',
        'br',
        'hr',
        'blockquote'
    ];

    /**
     * @var string Метка переноса строки в тегах по умолчанию
     */
    public const NEWLINE_MARKER = 'data-typograph-new-line';

    /**
     * Временно заменяет переносы строк внутри тегов на атрибут-метку (по умолчанию data-typograph-new-line).
     * Содержимое между тегами не изменяется.
     *
     * @param string $html
     * @param string $marker Метка, которой заменяются переносы строк; должна отсутствовать в исходном тексте
     * @return string
     */
    public static function replaceNewlinesInTags(string $html, string $marker = self::NEWLINE_MARKER): string
    {
        if (!str_contains($html, '<')) {
            return $html;
        }

        return preg_replace_callback(
            '/<([^>]*)>/',
            function ($matches) use ($marker) {
                $content = $matches[1];
                $content = str_replace(["\r\n", "\r", "\n"], ' ' . $marker . ' ', $content);
                return '<' . $content . '>';
            },
            $html
        );
    }

    /**
     * Содержит ли строка многострочный открывающий тег
     *
     * @param string $input
     * @param string $marker Метка, переданная в replaceNewlinesInTags
     * @return bool
     */
    public static function isStringContainsMultilineOpeningTag(string $input, string $marker = self::NEWLINE_MARKER): bool
    {
        return str_contains($input, ' ' . $marker . ' ');
    }

    /**
     * Восстанавливает переносы строк из временных меток
     *
     * @param string $html
     * @param string $eol Перевод строки, которым заменяются временные метки
     * @param string $marker Метка, переданная в replaceNewlinesInTags
     * @return array|string
     */
    public static function restoreNewlinesInTags(
        string $html,
        string $eol = "\n",
        string $marker = self::NEWLINE_MARKER
    ): array|string
    {
        return str_replace(' ' . $marker . ' ', $eol, $html);
    }

}
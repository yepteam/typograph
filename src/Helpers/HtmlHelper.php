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
     * Временно заменяет переносы строк:
     * * - в открывающих тегах на атрибут-метку (по умолчанию data-typograph-new-line)
     * * - внутри script, style, pre на временную метку [:TYPOGRAPH_NEW_LINE:]
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

        // Сначала обрабатываем специальные теги (script, style, pre)
        $html = preg_replace_callback(
            '/(<(script|style|pre)\b[^>]*>)(.*?)(<\/\2>)/is',
            function ($matches) use ($marker) {
                $openTag = $matches[1];
                $content = $matches[3];
                $closeTag = $matches[4];

                // Обрабатываем переносы строк в открывающем теге
                $openTag = preg_replace('/\r\n|\r|\n/', ' ' . $marker . ' ', $openTag);

                return $openTag . $content . $closeTag;
            },
            $html
        );

        // Затем обрабатываем все остальные теги
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
        // Восстанавливаем переносы строк в специальных тегах
        $html = preg_replace_callback(
            '/(<(script|style|pre)\b[^>]*>)(.*?)(<\/\2>)/is',
            function ($matches) {
                $openTag = $matches[1];
                $content = $matches[3];
                $closeTag = $matches[4];

                return $openTag . $content . $closeTag;
            },
            $html
        );

        // Восстанавливаем переносы строк в обычных тегах
        return str_replace(' ' . $marker . ' ', $eol, $html);
    }

}
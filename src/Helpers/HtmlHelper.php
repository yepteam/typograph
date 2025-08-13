<?php

namespace Yepteam\Typograph\Helpers;

class HtmlHelper
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
     * Временно заменяет переносы строк:
     * * - в открывающих тегах на атрибут data-typograph-new-line
     * * - внутри script, style, pre на временную метку [:TYPOGRAPH_NEW_LINE:]
     *
     * @param string $html
     * @return string
     */
    public static function replaceNewlinesInTags(string $html): string
    {
        if (!str_contains($html, '<')) {
            return $html;
        }

        // Сначала обрабатываем специальные теги (script, style, pre)
        $html = preg_replace_callback(
            '/(<(script|style|pre)\b[^>]*>)(.*?)(<\/\2>)/is',
            function ($matches) {
                $openTag = $matches[1];
                $content = $matches[3];
                $closeTag = $matches[4];

                // Обрабатываем переносы строк в открывающем теге
                $openTag = preg_replace('/\r\n|\r|\n/', ' data-typograph-new-line ', $openTag);

                return $openTag . $content . $closeTag;
            },
            $html
        );

        // Затем обрабатываем все остальные теги
        return preg_replace_callback(
            '/<([^>]*)>/',
            function ($matches) {
                $content = $matches[1];
                $content = str_replace(["\r\n", "\r", "\n"], ' data-typograph-new-line ', $content);
                return '<' . $content . '>';
            },
            $html
        );
    }

    /**
     * Содержит ли строка многострочный открывающий тег
     *
     * @param string $input
     * @return bool
     */
    public static function isStringContainsMultilineOpeningTag(string $input): bool
    {
        return str_contains($input, 'data-typograph-new-line');
    }

    /**
     * Восстанавливает переносы строк из временных меток
     *
     * @param string $html
     * @return array|string
     */
    public static function restoreNewlinesInTags(string $html): array|string
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

        // Восстанавливаем атрибуты data-typograph-new-line в обычных тегах
        return str_replace(' data-typograph-new-line ', PHP_EOL, $html);
    }

}
<?php

namespace Yepteam\Typograph;

use Yepteam\Typograph\Helpers\HtmlHelper;
use Yepteam\Typograph\Rules\Formatting\HtmlEntities;

class Tokenizer
{
    /**
     * @var array[] Шаблоны для распознавания токенов
     * @psalm-var array<array{type: string, name: string, pattern: string}>
     */
    const TOKEN_PATTERNS = [
        [
            'type' => 'entity',
            'name' => 'entity',
            'pattern' => '/&(?!(?:ndash|mdash)\b)(?:[a-zA-Z0-9]+|#[0-9]{1,7}|#x[0-9a-fA-F]{1,6});/'
        ],
        [
            'type' => 'tag',
            'name' => 'doctype',
            'pattern' => '/<!DOCTYPE\s[^>]+>/i',
        ],
        [
            'type' => 'tag',
            'name' => 'xml-declaration',
            'pattern' => '/<\?xml\b[^?>]*\?>/i',
        ],
        [
            'type' => 'tag',
            'name' => 'php-tag',
            'pattern' => '/<\?.*?\?>/s',
        ],
        [
            'type' => 'tag',
            'name' => 'comment',
            'pattern' => '/<!--.*?-->/s',
        ],
        [
            'type' => 'tag',
            'name' => 'script',
            'pattern' => '/<script\b[^>]*>.*?<\/script>/is',
        ],
        [
            'type' => 'tag',
            'name' => 'style',
            'pattern' => '/<style\b[^>]*>.*?<\/style>/is',
        ],
        [
            'type' => 'tag',
            'name' => 'pre',
            'pattern' => '/<pre\b[^>]*>.*?<\/pre>/is',
        ],
        [
            'type' => 'tag',
            'name' => 'tag',
            'pattern' => '/<\/?[a-z][a-z0-9]*(?:[:-][a-z0-9]+)*(?:\s+[a-z0-9_-]+(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+))?)*\s*\/?>/i',
        ],
        [
            'type' => 'tag',
            'name' => 'shortcode',
            'pattern' => '/\[(\/)?[a-z][a-z0-9_-]*\b([^\]\"]*|\".*?\")*\]/is',
        ],
        [
            'type' => 'url',
            'name' => 'url',
            'pattern' => '#^(https?://|www\.)[^\s<>"\']+#i',
        ],
        [
            'type' => 'email',
            'name' => 'email',
            'pattern' => '/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}\b/i',
        ],
        [
            'type' => 'guid',
            'name' => 'guid',
            'pattern' => '/(?:\{|\(|)?[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}(?:\}|\)|)?/',
        ],
        [
            'type' => 'double-hyphen',
            'name' => 'double-hyphen',
            'pattern' => '/--/',
        ],
        [
            'type' => 'initial',
            'name' => 'initial',
            'pattern' => '/(?<=^|[(])\s*\K\p{Lu}\./u',
        ],
        [
            'type' => 'number',
            'name' => 'number',
            'pattern' => '/\d+([.,]\d+)?/u',
        ],
        [
            'type' => 'copy',
            'name' => 'copy',
            'pattern' => '/\([СсCc]\)/u',
        ],
        [
            'type' => 'reg',
            'name' => 'reg',
            'pattern' => '/\([R|r]\)/',
        ],
        [
            'type' => 'trade',
            'name' => 'trade',
            'pattern' => '/\(tm\)/i',
        ],
        [
            'type' => 'word',
            'name' => 'word',
            'pattern' => '/\b[\p{L}\p{M}\p{Nd}\'‘’ʼ]+(?:-(?!\d)[\p{L}\p{M}\p{Nd}\'‘’ʼ]+)*\b/u',
        ],
        [
            'type' => 'new-line',
            'name' => 'new-line',
            'pattern' => '/\R/',
        ],
        [
            'type' => 'tab',
            'name' => 'tab',
            'pattern' => '/\t/u',
        ],
        [
            'type' => 'hyphen',
            'name' => 'hyphen',
            'pattern' => '/-/',
        ],
        [
            'type' => 'ndash',
            'name' => 'ndash',
            'pattern' => '/–|&ndash;/',
        ],
        [
            'type' => 'mdash',
            'name' => 'mdash',
            'pattern' => '/—|&mdash;/',
        ],
        [
            'type' => 'minus',
            'name' => 'minus',
            'pattern' => '/−|&minus;/',
        ],
        [
            'type' => 'plus',
            'name' => 'plus',
            'pattern' => '/\+|&plus;/',
        ],
        [
            'type' => 'equals',
            'name' => 'equals',
            'pattern' => '/\=|&equals;/',
        ],
        [
            'type' => 'slash',
            'name' => 'slash',
            'pattern' => '/\//',
        ],
        [
            'type' => 'backslash',
            'name' => 'backslash',
            'pattern' => '/\\\\/',
        ],
        [
            'type' => 'hellip',
            'name' => 'hellip',
            'pattern' => '/\…|&hellip;/',
        ],
        [
            'type' => 'three-dots',
            'name' => 'three-dots',
            'pattern' => '/\.{3}(?!\.)/',
        ],
        [
            'type' => 'comma',
            'name' => 'comma',
            'pattern' => '/,/u',
        ],
        [
            'type' => 'dot',
            'name' => 'dot',
            'pattern' => '/\./',
        ],
        [
            'type' => 'quote',
            'name' => 'Prime',
            'pattern' => '/″|&Prime;/'
        ],
        [
            'type' => 'quote',
            'name' => 'prime',
            'pattern' => '/′|&prime;/'
        ],
        [
            'type' => 'quote',
            'name' => 'quot',
            'pattern' => '/"|&quot;/'
        ],
        [
            'type' => 'quote',
            'name' => 'ldquo',
            'pattern' => '/“|&laquo;/'
        ],
        [
            'type' => 'quote',
            'name' => 'rdquo',
            'pattern' => '/”|&rdquo;/'
        ],
        [
            'type' => 'quote',
            'name' => 'bdquo',
            'pattern' => '/„|&bdquo;/'
        ],
        [
            'type' => 'quote',
            'name' => 'laquo',
            'pattern' => '/«|&laquo;/'
        ],
        [
            'type' => 'quote',
            'name' => 'raquo',
            'pattern' => '/»|&raquo;/'
        ],
        [
            'type' => 'apos',
            'name' => 'apos',
            'pattern' => '/\'/',
        ],
        [
            'type' => 'nbsp',
            'name' => 'nbsp',
            'pattern' => '/\xA0|&nbsp;/u',
        ],
        [
            'type' => 'space',
            'name' => 'space',
            'pattern' => '/\s/',
        ],
        [
            'type' => 'deg',
            'name' => 'deg',
            'pattern' => '/°|℃|℉/u',
        ],
        [
            'type' => 'punctuation',
            'name' => 'punctuation',
            'pattern' => '/\p{P}/u',
        ],
        [
            'type' => 'emoji',
            'name' => 'emoji',
            'pattern' => '/\p{Emoji}/u',
        ],
    ];

    private array $specialTags = [];

    /**
     * Разбивает текст на токены с учетом переносов строк
     *
     * @param string $input Входной текст для токенизации
     * @return array[] Массив токенов
     * @psalm-return array<array{type: string, value: string}>
     */
    public function tokenize(string $input): array
    {
        $input = trim($input);

        // Сначала выделяем специальные теги (script, style, pre) с их содержимым
        $input = $this->preserveSpecialTags($input);

        // Заменяем все неразрывные пробелы на обычные (потом будет не оптимально)
        $input = str_replace([' ', ' '], ' ', $input);

        // Заменяем все неразрывные дефисы на обычные
        $input = str_replace([HtmlEntities::decodeEntity('&#8209;')], '-', $input);

        $input = Helpers\HtmlHelper::replaceNewlinesInTags($input);

        $lines = explode(PHP_EOL, $input);

        $all_tokens = [];

        foreach ($lines as $index => $line) {
            $tokens = self::tokenizeLine($line);
            if ($index > 0) {
                $all_tokens[] = [
                    'type' => 'new-line',
                    'value' => PHP_EOL,
                ];
            }
            foreach ($tokens as $token) {
                $all_tokens[] = $token;
            }
        }

        return $all_tokens;
    }

    /**
     * Разбивает одну строку текста на токены
     *
     * @param string $input Входная строка для токенизации
     * @return array[] Массив токенов
     * @psalm-return array<array{type: string, value: string}>
     */
    public function tokenizeLine(string $input): array
    {
        if (mb_strlen($input) === 0) {
            return [];
        }

        $tokens = [];

        $lines = explode(PHP_EOL, $input);

        $lines = array_map(function ($line) {
            if (HtmlHelper::isStringContainsMultilineOpeningTag($line)) {
                return $line;
            }

            // Сохраняем табуляции только в начале строки
            $leadingTabs = '';
            $restOfLine = $line;

            // Выделяем ведущие табуляции
            if (preg_match('/^(\t+)(.*)/u', $line, $matches)) {
                $leadingTabs = $matches[1];
                $restOfLine = $matches[2];
            }

            // Заменяем все пробелы и табуляции в оставшейся части на один пробел
            $restProcessed = preg_replace('/[\s\t]+/u', ' ', trim($restOfLine));

            return $leadingTabs . $restProcessed;
        }, $lines);

        $processedInput = implode(PHP_EOL, $lines);

        $processedInput = HtmlHelper::restoreNewlinesInTags($processedInput);

        $offset = 0;
        $length = mb_strlen($processedInput, 'UTF-8');

        while ($offset < $length) {
            $foundToken = null;
            $substr = mb_substr($processedInput, $offset, null, 'UTF-8');

            // Проверяем, не начинается ли подстрока с сохраненного специального тега
            if (preg_match('/^\[SPECIAL_TAG:(\w+):(\d+)]/', $substr, $tagMatches)) {
                $tagType = $tagMatches[1];
                $tagId = $tagMatches[2];
                $tagContent = $this->restoreSpecialTag($tagType, $tagId);

                $tokens[] = [
                    'type' => 'tag',
                    'name' => strtolower($tagType),
                    'value' => $tagContent,
                ];

                $offset += mb_strlen($tagMatches[0], 'UTF-8');
                continue;
            }

            // Обычная обработка токенов
            foreach (self::TOKEN_PATTERNS as $pattern) {
                if (!preg_match($pattern['pattern'], $substr, $matches, PREG_OFFSET_CAPTURE)) {
                    continue;
                }

                // Проверяем, что совпадение найдено в начале строки
                if ($matches[0][1] != 0) {
                    continue;
                }

                $tokenValue = $matches[0][0];

                // Для тегов извлекаем название тега
                if ($pattern['type'] === 'tag' && $pattern['name'] !== 'shortcode') {
                    // Проверяем, является ли это закрывающим тегом
                    if (preg_match('/<\/([a-z][a-z0-9]*(?:[:-][a-z0-9]+)*)/i', $tokenValue, $tagMatches)) {
                        $tagName = $tagMatches[1];
                    } // Или открывающим/самозакрывающимся тегом
                    elseif (preg_match('/<([a-z][a-z0-9]*(?:[:-][a-z0-9]+)*)/i', $tokenValue, $tagMatches)) {
                        $tagName = $tagMatches[1];
                    } else {
                        $tagName = $pattern['name'] ?? 'unknown';
                    }

                    $foundToken = [
                        'type' => $pattern['type'],
                        'name' => strtolower($tagName), // сохраняем имя тега в lowercase
                        'value' => $tokenValue,
                    ];
                    break;
                }

                $foundToken = [
                    'type' => $pattern['type'],
                    'name' => $pattern['name'],
                    'value' => $tokenValue,
                ];
                break;
            }

            // Если токен найден
            if ($foundToken) {
                $tokens[] = $foundToken;
                $offset += mb_strlen($foundToken['value'], 'UTF-8');
                continue;
            }

            // Токен не найден — сохраняем как одиночный символ
            $char = mb_substr($processedInput, $offset, 1, 'UTF-8');
            $tokens[] = [
                'type' => 'char',
                'value' => $char
            ];
            $offset++;
        }

        return $tokens;
    }

    /**
     * Собирает текст из массива токенов
     *
     * @param array[] $tokens Массив токенов
     * @psalm-param array<array{type: string, value: string}> $tokens
     * @return string Собранный текст
     */
    public function toString(array $tokens): string
    {
        $result = '';
        foreach ($tokens as $token) {
            // Добавляем проверку, чтобы пропускать "удаленные" токены
            if (isset($token['type']) && $token['type'] === 'empty') {
                continue;
            }

            $result .= $token['value'];
        }
        return $result;
    }

    /**
     * Сохраняет специальные теги (script, style, pre) и блочные шорткоды с их содержимым.
     * Заменяет их на временные метки в тексте.
     *
     * @param string $input
     * @return string
     */
    private function preserveSpecialTags(string $input): string
    {
        $this->specialTags = [];

        // 1. Сначала обрабатываем блочные шорткоды [tag]...[/tag]
        // Это более надежный паттерн, который правильно обработает даже вложенные шорткоды.
        // Он ищет открывающий тег, затем лениво ищет любое содержимое до тех пор,
        // пока не встретит соответствующий ему закрывающий тег.
        $shortcodeBlockPattern = '/\[([a-z0-9_-]+)\b[^]]*]((?:(?!\[\/])[\s\S])*)\[\/]/is';

        $input = preg_replace_callback($shortcodeBlockPattern, function ($matches) {
            // Используем имя шорткода (например, 'embed') в качестве типа тега
            $tagType = strtolower($matches[1]);
            $id = count($this->specialTags);
            $this->specialTags["$tagType:$id"] = $matches[0];
            return "[SPECIAL_TAG:$tagType:$id]";
        }, $input);

        // 2. Затем обрабатываем остальные специальные HTML теги как и раньше
        $patterns = [
            'doctype' => '/<!DOCTYPE\s[^>]+>/i',
            'comment' => '/<!--.*?-->/s',
            'script' => '/<script\b[^>]*>[\s\S]*?<\/script>/is',
            'style' => '/<style\b[^>]*>[\s\S]*?<\/style>/is',
            'pre' => '/<pre\b[^>]*>[\s\S]*?<\/pre>/is',
        ];

        foreach ($patterns as $tagType => $pattern) {
            $input = preg_replace_callback($pattern, function ($matches) use ($tagType) {
                $id = count($this->specialTags);
                $this->specialTags["{$tagType}:{$id}"] = $matches[0];
                return "[SPECIAL_TAG:{$tagType}:{$id}]";
            }, $input);
        }

        return $input;
    }

    /**
     * Восстанавливает специальный тег по его типу и ID
     *
     * @param string $tagType
     * @param int $tagId
     * @return string
     */
    private function restoreSpecialTag(string $tagType, int $tagId): string
    {
        $key = "$tagType:$tagId";
        return $this->specialTags[$key] ?? '';
    }
}
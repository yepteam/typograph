<?php

namespace Yepteam\Typograph;

use Yepteam\Typograph\Helpers\HtmlHelper;
use Yepteam\Typograph\Rules\Formatting\HtmlEntities;

class Tokenizer
{
    /**
     * @var array Массив метрик токенизатора
     */
    private array $metrics = [
        'countOfLines' => 0,
        'countOfTokens' => 0,
        'tokenizationTime' => 0.0
    ];

    /**
     * @var array[] Шаблоны для распознавания токенов
     * @psalm-var array<array{type: string, name: string, pattern: string}>
     */
    const TOKEN_PATTERNS = [
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
            'type' => 'initial',
            'name' => 'initial',
            'pattern' => '/(?<=^|[(])\s*\K\p{Lu}\./u',
        ],
        [
            'type' => 'double-hyphen',
            'name' => 'double-hyphen',
            'pattern' => '/--/',
        ],
        [
            'type' => 'number',
            'name' => 'number',
            'pattern' => '/\d+([.,]\d+)?/u',
        ],
        [
            'type' => 'copy',
            'name' => 'copy',
            'pattern' => '/\([СсCc]\)|&copy;/u',
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
            'pattern' => '/“|&ldquo;/'
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
            'type' => 'emoji',
            'name' => 'emoji',
            'pattern' => '/\p{Emoji}/u',
        ],
        [
            'type' => 'entity',
            'name' => 'entity',
            'pattern' => '/&(?:[a-zA-Z0-9]+|#[0-9]{1,7}|#x[0-9a-fA-F]{1,6});/'
        ],
        [
            'type' => 'punctuation',
            'name' => 'punctuation',
            'pattern' => '/\p{P}/u',
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
        // Обнуляем метрики
        $this->metrics['tokenizationTime'] = 0.0;

        $tokenizationStart = microtime(true);

        $input = trim($input, " \n\r\v\0");

        // Сначала выделяем специальные теги (script, style, pre) с их содержимым
        $input = $this->preserveSpecialTags($input);

        // Заменяем все неразрывные пробелы на обычные (потом будет не оптимально)
        $input = str_replace([' ', ' ', '&nbsp;'], ' ', $input);

        // Заменяем все неразрывные дефисы на обычные
        $input = str_replace([HtmlEntities::decodeEntity('&#8209;')], '-', $input);

        $input = Helpers\HtmlHelper::replaceNewlinesInTags($input);

        // Приводим все CR|LF к \n
        $input = preg_replace('/\r\n|\r/', "\n", $input);
        $lines = explode("\n", $input);

        $this->metrics['countOfLines'] = count($lines);

        $all_tokens = [];

        // Обрабатываем первую строку отдельно
        $tokens = self::tokenizeLine($lines[0]);
        foreach ($tokens as $token) {
            $all_tokens[] = $token;
        }

        // Обрабатываем остальные строки с добавлением токена новой строки
        for ($i = 1; $i < count($lines); $i++) {
            $all_tokens[] = [
                'type' => 'new-line',
                'value' => PHP_EOL,
            ];
            $tokens = self::tokenizeLine($lines[$i]);
            foreach ($tokens as $token) {
                $all_tokens[] = $token;
            }
        }

        $this->metrics['countOfTokens'] = count($all_tokens);
        $this->metrics['tokenizationTime'] = microtime(true) - $tokenizationStart;

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

        $lines = explode('\n', $input);

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
                $restOfLine = trim($matches[2]);
            }

            // Заменяем все пробелы и табуляции в оставшейся части на один пробел
            $restOfLine = preg_replace('/[\s\t]+/u', ' ', trim($restOfLine));

            return $leadingTabs . $restOfLine;
        }, $lines);

        $processedInput = implode(PHP_EOL, $lines);

        $processedInput = HtmlHelper::restoreNewlinesInTags($processedInput);

        $offset = 0;
        $length = mb_strlen($processedInput, 'UTF-8');

        while ($offset < $length) {
            $foundToken = null;
            $substr = mb_substr($processedInput, $offset, null, 'UTF-8');

            // Проверяем, не начинается ли подстрока с сохраненного специального тега
            if (str_contains($substr, 'SPECIAL_TAG') && preg_match('/^\[SPECIAL_TAG:(\w+):(\d+)]/', $substr, $tagMatches)) {
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
     * Заменяет специальные теги на временные метки в тексте.
     *
     * @param string $input
     * @return string
     */
    private function preserveSpecialTags(string $input): string
    {
        if (!str_contains($input, '<')) {
            return $input;
        }

        $this->specialTags = [];

        // Вспомогательная функция для создания временной метки
        $createPlaceholder = function (array $matches, string $tagType): string {
            $id = count($this->specialTags);
            $placeholder = $tagType . ":" . $id;
            $this->specialTags[$placeholder] = $matches[0];
            return "[SPECIAL_TAG:{$placeholder}]";
        };

        $patternsAndCallbacks = [
            '/<!DOCTYPE\s[^>]+>/i' => function ($m) use ($createPlaceholder) {
                return $createPlaceholder($m, 'doctype');
            },
            '/<!--.*?-->/s' => function ($m) use ($createPlaceholder) {
                return $createPlaceholder($m, 'comment');
            },
            '/<script\b[^>]*>[\s\S]*?<\/script>/is' => function ($m) use ($createPlaceholder) {
                return $createPlaceholder($m, 'script');
            },
            '/<style\b[^>]*>[\s\S]*?<\/style>/is' => function ($m) use ($createPlaceholder) {
                return $createPlaceholder($m, 'style');
            },
            '/<pre\b[^>]*>[\s\S]*?<\/pre>/is' => function ($m) use ($createPlaceholder) {
                return $createPlaceholder($m, 'pre');
            },
        ];

        return preg_replace_callback_array($patternsAndCallbacks, $input);
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

    /**
     * @return array Массив метрик токенизатора
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }
}
<?php

namespace Yepteam\Typograph;

class Tokenizer
{
    /**
     * @var array[] Шаблоны для распознавания токенов
     * @psalm-var array<array{type: string, name: string, pattern: string}>
     */
    const TOKEN_PATTERNS = [
        [
            'type' => 'tag',
            'name' => 'tag',
            'pattern' => '/<\/?[a-z][a-z0-9]*\b[^>]*>|<!DOCTYPE\s[^>]+>|<!--(?:[^-]|-(?!->))*-->/i',
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
            'pattern' => '/\d+/u',
        ],
        [
            'type' => 'copy',
            'name' => 'copy',
            'pattern' => '/\(C\)/',
        ],
        [
            'type' => 'reg',
            'name' => 'reg',
            'pattern' => '/\([R|r]\)/',
        ],
        [
            'type' => 'word',
            'name' => 'word',
            'pattern' => '/\b[\p{L}\p{M}\p{Nd}\'‘’ʼ]+(?:-[\p{L}\p{M}\p{Nd}\'‘’ʼ]+)*\b/u',
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
        ]
    ];

    /**
     * Разбивает текст на токены с учетом переносов строк
     *
     * @param string $input Входной текст для токенизации
     * @return array[] Массив токенов
     * @psalm-return array<array{type: string, value: string}>
     */
    public function tokenize(string $input): array
    {
        $input = html_entity_decode($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $input = html_entity_decode($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $input = trim($input);

        // Заменяем все неразрывные пробелы на обычные (потом будет не оптимально)
        $input = str_replace(' ', ' ', $input);

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
        $input = html_entity_decode($input);
        $input = html_entity_decode($input);

        $tokens = [];

        // Удаляем лишние пробелы в каждой строке и заменяем повторяющиеся пробелы на один
        $lines = explode(PHP_EOL, $input);
        $processedLines = array_map(function ($line) {
            // Заменяем повторяющиеся пробелы на один, но сохраняем табуляции и др.
            return preg_replace('/ +/u', ' ', trim($line));
        }, $lines);
        $processedInput = implode(PHP_EOL, $processedLines);

        $offset = 0;
        $length = mb_strlen($processedInput, 'UTF-8');

        while ($offset < $length) {
            $foundToken = null;
            $substr = mb_substr($processedInput, $offset, null, 'UTF-8');

            foreach (self::TOKEN_PATTERNS as $pattern) {
                if (!preg_match($pattern['pattern'], $substr, $matches, PREG_OFFSET_CAPTURE)) {
                    continue;
                }

                // Проверяем, что совпадение найдено в начале строки
                if ($matches[0][1] == 0) {
                    $tokenValue = $matches[0][0];
                    $foundToken = [
                        'type' => $pattern['type'],
                        'value' => $tokenValue,
                    ];
                    break;
                }
            }

            if ($foundToken) {
                $tokens[] = $foundToken;
                $offset += mb_strlen($foundToken['value'], 'UTF-8');
            } else {
                $char = mb_substr($processedInput, $offset, 1, 'UTF-8');
                $tokens[] = [
                    'type' => 'char',
                    'value' => $char
                ];
                $offset++;
            }
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
            $result .= $token['value'];
        }
        return $result;
    }
}
<?php

namespace Yepteam\Typograph;

use Yepteam\Typograph\Helpers\HtmlEntityHelper;
use Yepteam\Typograph\Helpers\HtmlHelper;

final class Tokenizer
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
            'pattern' => '/<\/?[a-z][a-z0-9]*(?:[:-][a-z0-9]+)*(?:\s+[@:.a-z0-9_-]+(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+))?)*\s*\/?>/i',
        ],
        [
            'type' => 'tag',
            'name' => 'shortcode',
            'pattern' => '/\[(\/)?[a-z][a-z0-9_-]*\b([^\]\"]*|\".*?\")*\]/is',
        ],
        [
            'type' => 'url',
            'name' => 'url',
            'pattern' => '#(https?://|www\.)[^\s<>"\']+#i',
        ],
        [
            'type' => 'email',
            'name' => 'email',
            'pattern' => '/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}\b/i',
        ],
        [
            'type' => 'guid',
            'name' => 'guid',
            'pattern' => '/(?:\{|\(|)?[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}(?:\}|\)|)?/',
        ],
        [
            'type' => 'initial',
            'name' => 'initial',
            'pattern' => '/\s*\K\p{Lu}\./u',
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
            'pattern' => '/(?=\w)[\p{L}\p{M}\p{Nd}\'‘’ʼ]+(?:-(?!\d)[\p{L}\p{M}\p{Nd}\'‘’ʼ]+)*\b/u',
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

    /**
     * @var array<string, string> Содержимое специальных тегов, замененных временными метками
     */
    private array $specialTags = [];

    /**
     * @var string Уникальный префикс временных меток специальных тегов (свой для каждого вызова tokenize)
     */
    private string $placeholderPrefix = '';

    /**
     * @var string Перевод строки, используемый во входном тексте (сохраняется в результате)
     */
    private string $eol = "\n";

    /**
     * @var array|null Шаблоны TOKEN_PATTERNS, привязанные к текущей позиции (модификатор A)
     */
    private static ?array $anchoredPatterns = null;

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

        // Состояние предыдущего вызова не должно влиять на текущий
        $this->specialTags = [];
        $this->placeholderPrefix = 'TYPOGRAPH_TAG_' . bin2hex(random_bytes(8));

        // Регулярные выражения с модификатором /u не работают с невалидным UTF-8:
        // возвращаем текст одним токеном, чтобы не потерять данные
        if (!mb_check_encoding($input, 'UTF-8')) {
            $this->metrics['countOfLines'] = 1;
            $this->metrics['countOfTokens'] = 1;
            $this->metrics['tokenizationTime'] = microtime(true) - $tokenizationStart;

            return [
                [
                    'type' => 'char',
                    'name' => 'invalid-utf8',
                    'value' => $input,
                ],
            ];
        }

        $input = trim($input, " \n\r\v\0");

        // Запоминаем стиль переводов строк входного текста
        $this->eol = preg_match('/\r\n|\r|\n/', $input, $eolMatches) ? $eolMatches[0] : "\n";

        // Сначала выделяем специальные теги (script, style, pre) с их содержимым
        $input = $this->preserveSpecialTags($input);

        // Заменяем все неразрывные пробелы на обычные (потом будет не оптимально)
        $input = str_replace([' ', ' ', '&nbsp;'], ' ', $input);

        // Заменяем все неразрывные дефисы на обычные
        $input = str_replace([HtmlEntityHelper::decodeEntity('&#8209;')], '-', $input);

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
                'value' => $this->eol,
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

        $processedInput = self::normalizeLineWhitespace($input);

        $processedInput = HtmlHelper::restoreNewlinesInTags($processedInput, $this->eol);

        // PHP проверяет корректность UTF-8 всего остатка строки при каждом вызове preg_match с модификатором /u
        // и ненулевым смещением, если строка еще не помечена как валидная. Пометка ставится только после
        // /u-проверки с нулевого смещения — делаем ее сразу, иначе токенизация длинной строки становится квадратичной.
        preg_match('//u', $processedInput);

        $placeholderPattern = $this->placeholderPrefix !== ''
            ? '/\[' . preg_quote($this->placeholderPrefix, '/') . ':(\w+):(\d+)]/A'
            : null;

        // Смещение в байтах: шаблоны применяются с привязкой к текущей позиции,
        // без копирования остатка строки, поэтому время работы линейно от длины строки
        $offset = 0;
        $length = strlen($processedInput);

        while ($offset < $length) {
            $foundToken = null;
            $consumedLength = 0;

            // Проверяем, не начинается ли подстрока с сохраненного специального тега
            if (
                $placeholderPattern !== null &&
                $processedInput[$offset] === '[' &&
                preg_match($placeholderPattern, $processedInput, $tagMatches, 0, $offset)
            ) {
                $tagType = $tagMatches[1];
                $tagId = (int)$tagMatches[2];
                $tagContent = $this->restoreSpecialTag($tagType, $tagId);

                $tokens[] = [
                    'type' => 'tag',
                    'name' => strtolower($tagType),
                    'value' => $tagContent,
                ];

                $offset += strlen($tagMatches[0]);
                continue;
            }

            // Обычная обработка токенов
            foreach (self::getAnchoredPatterns() as $pattern) {
                if (!preg_match($pattern['pattern'], $processedInput, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                    continue;
                }

                // Проверяем, что совпадение начинается с текущей позиции (\K может сдвинуть начало)
                if ($matches[0][1] !== $offset || $matches[0][0] === '') {
                    continue;
                }

                $tokenValue = $matches[0][0];
                $consumedLength = strlen($tokenValue);

                // Совпадение захватило временную метку специального тега (например, URL вплотную к <pre>)
                $placeholderStart = '[' . $this->placeholderPrefix . ':';
                if ($this->specialTags && str_contains($tokenValue, $placeholderStart)) {
                    if ($pattern['type'] === 'tag') {
                        // Внутри тега или шорткода — восстанавливаем исходное содержимое
                        $tokenValue = $this->restorePlaceholders($tokenValue);
                    } else {
                        // Обычный токен заканчивается перед меткой, метка станет отдельным токеном
                        $tokenValue = substr($tokenValue, 0, strpos($tokenValue, $placeholderStart));
                        $consumedLength = strlen($tokenValue);
                    }
                }

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
                $offset += $consumedLength;
                continue;
            }

            // Токен не найден — сохраняем как одиночный символ (или байт, если UTF-8 невалиден)
            $char = preg_match('/./suA', $processedInput, $charMatches, 0, $offset)
                ? $charMatches[0]
                : $processedInput[$offset];
            $tokens[] = [
                'type' => 'char',
                'value' => $char
            ];
            $offset += strlen($char);
        }

        return $tokens;
    }

    /**
     * Схлопывает пробельные символы строки, сохраняя ведущие табуляции.
     * Строки с многострочным открывающим тегом не изменяются.
     *
     * @param string $line
     * @return string
     */
    private static function normalizeLineWhitespace(string $line): string
    {
        if (HtmlHelper::isStringContainsMultilineOpeningTag($line)) {
            return $line;
        }

        // Сохраняем табуляции только в начале строки
        $leadingTabs = '';
        $restOfLine = $line;

        // Выделяем ведущие табуляции
        if (preg_match('/^(\t+)(.*)/us', $line, $matches)) {
            $leadingTabs = $matches[1];
            $restOfLine = $matches[2];
        }

        // Заменяем все пробелы и табуляции в оставшейся части на один пробел
        $restOfLine = trim($restOfLine);
        $restOfLine = preg_replace('/[\s\t]+/u', ' ', $restOfLine) ?? $restOfLine;

        return $leadingTabs . $restOfLine;
    }

    /**
     * Возвращает шаблоны токенов, привязанные к позиции начала поиска (модификатор A).
     *
     * @return array[]
     */
    private static function getAnchoredPatterns(): array
    {
        if (self::$anchoredPatterns === null) {
            self::$anchoredPatterns = array_map(function (array $pattern) {
                $pattern['pattern'] .= 'A';
                return $pattern;
            }, self::TOKEN_PATTERNS);
        }

        return self::$anchoredPatterns;
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

        // Вспомогательная функция для создания временной метки
        $createPlaceholder = function (array $matches, string $tagType): string {
            $id = count($this->specialTags);
            $placeholder = $tagType . ":" . $id;
            $this->specialTags[$placeholder] = $matches[0];
            return "[{$this->placeholderPrefix}:{$placeholder}]";
        };

        $patternsAndCallbacks = [
            '/<!DOCTYPE\s[^>]+>/i' => function ($m) use ($createPlaceholder) {
                return $createPlaceholder($m, 'doctype');
            },
            '/<!--.*?-->/s' => function ($m) use ($createPlaceholder) {
                return $createPlaceholder($m, 'comment');
            },
            '/<\?(?:php|=)?\b.*?\?>/s' => function ($m) use ($createPlaceholder) {
                return $createPlaceholder($m, 'php');
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
     * Восстанавливает все временные метки специальных тегов внутри строки
     *
     * @param string $value
     * @return string
     */
    private function restorePlaceholders(string $value): string
    {
        return preg_replace_callback(
            '/\[' . preg_quote($this->placeholderPrefix, '/') . ':(\w+):(\d+)]/',
            fn(array $m) => $this->restoreSpecialTag($m[1], (int)$m[2]),
            $value
        ) ?? $value;
    }

    /**
     * @return array Массив метрик токенизатора
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }
}
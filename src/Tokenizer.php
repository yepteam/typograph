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
     * Минимальная версия PCRE2 со свойством Unicode \p{Emoji} (PHP 8.2+)
     */
    const EMOJI_PROPERTY_MIN_PCRE_VERSION = '10.40';

    /**
     * Замена \p{Emoji} для PCRE2 < 10.40 (PHP 8.1): кодовые точки со свойством Emoji,
     * сгенерированные из \p{Emoji} PCRE2 10.44
     */
    const EMOJI_FALLBACK_PATTERN = '/['
        . '\x{23}\x{2A}\x{30}-\x{39}\x{A9}\x{AE}\x{203C}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}'
        . '\x{21A9}-\x{21AA}\x{231A}-\x{231B}\x{2328}\x{23CF}\x{23E9}-\x{23F3}\x{23F8}-\x{23FA}\x{24C2}'
        . '\x{25AA}-\x{25AB}\x{25B6}\x{25C0}\x{25FB}-\x{25FE}\x{2600}-\x{2604}\x{260E}\x{2611}\x{2614}-\x{2615}'
        . '\x{2618}\x{261D}\x{2620}\x{2622}-\x{2623}\x{2626}\x{262A}\x{262E}-\x{262F}\x{2638}-\x{263A}\x{2640}'
        . '\x{2642}\x{2648}-\x{2653}\x{265F}-\x{2660}\x{2663}\x{2665}-\x{2666}\x{2668}\x{267B}\x{267E}-\x{267F}'
        . '\x{2692}-\x{2697}\x{2699}\x{269B}-\x{269C}\x{26A0}-\x{26A1}\x{26A7}\x{26AA}-\x{26AB}'
        . '\x{26B0}-\x{26B1}\x{26BD}-\x{26BE}\x{26C4}-\x{26C5}\x{26C8}\x{26CE}-\x{26CF}\x{26D1}'
        . '\x{26D3}-\x{26D4}\x{26E9}-\x{26EA}\x{26F0}-\x{26F5}\x{26F7}-\x{26FA}\x{26FD}\x{2702}\x{2705}'
        . '\x{2708}-\x{270D}\x{270F}\x{2712}\x{2714}\x{2716}\x{271D}\x{2721}\x{2728}\x{2733}-\x{2734}\x{2744}'
        . '\x{2747}\x{274C}\x{274E}\x{2753}-\x{2755}\x{2757}\x{2763}-\x{2764}\x{2795}-\x{2797}\x{27A1}\x{27B0}'
        . '\x{27BF}\x{2934}-\x{2935}\x{2B05}-\x{2B07}\x{2B1B}-\x{2B1C}\x{2B50}\x{2B55}\x{3030}\x{303D}\x{3297}'
        . '\x{3299}\x{1F004}\x{1F0CF}\x{1F170}-\x{1F171}\x{1F17E}-\x{1F17F}\x{1F18E}\x{1F191}-\x{1F19A}'
        . '\x{1F1E6}-\x{1F1FF}\x{1F201}-\x{1F202}\x{1F21A}\x{1F22F}\x{1F232}-\x{1F23A}\x{1F250}-\x{1F251}'
        . '\x{1F300}-\x{1F321}\x{1F324}-\x{1F393}\x{1F396}-\x{1F397}\x{1F399}-\x{1F39B}\x{1F39E}-\x{1F3F0}'
        . '\x{1F3F3}-\x{1F3F5}\x{1F3F7}-\x{1F4FD}\x{1F4FF}-\x{1F53D}\x{1F549}-\x{1F54E}\x{1F550}-\x{1F567}'
        . '\x{1F56F}-\x{1F570}\x{1F573}-\x{1F57A}\x{1F587}\x{1F58A}-\x{1F58D}\x{1F590}\x{1F595}-\x{1F596}'
        . '\x{1F5A4}-\x{1F5A5}\x{1F5A8}\x{1F5B1}-\x{1F5B2}\x{1F5BC}\x{1F5C2}-\x{1F5C4}\x{1F5D1}-\x{1F5D3}'
        . '\x{1F5DC}-\x{1F5DE}\x{1F5E1}\x{1F5E3}\x{1F5E8}\x{1F5EF}\x{1F5F3}\x{1F5FA}-\x{1F64F}'
        . '\x{1F680}-\x{1F6C5}\x{1F6CB}-\x{1F6D2}\x{1F6D5}-\x{1F6D7}\x{1F6DC}-\x{1F6E5}\x{1F6E9}'
        . '\x{1F6EB}-\x{1F6EC}\x{1F6F0}\x{1F6F3}-\x{1F6FC}\x{1F7E0}-\x{1F7EB}\x{1F7F0}\x{1F90C}-\x{1F93A}'
        . '\x{1F93C}-\x{1F945}\x{1F947}-\x{1F9FF}\x{1FA70}-\x{1FA7C}\x{1FA80}-\x{1FA88}\x{1FA90}-\x{1FABD}'
        . '\x{1FABF}-\x{1FAC5}\x{1FACE}-\x{1FADB}\x{1FAE0}-\x{1FAE8}\x{1FAF0}-\x{1FAF8}'
        . ']/u';

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
            'pattern' => '/\([Rr]\)/',
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
            // Шаблон для PCRE2 < 10.40, где \p{Emoji} не компилируется
            'fallback' => self::EMOJI_FALLBACK_PATTERN,
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
     * @var string Уникальная метка переносов строк внутри тегов (своя для каждого вызова tokenize),
     * чтобы не спутать ее с таким же текстом во входных данных
     */
    private string $newlineMarker = HtmlHelper::NEWLINE_MARKER;

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
        $this->newlineMarker = HtmlHelper::NEWLINE_MARKER . '-' . bin2hex(random_bytes(8));

        // Регулярные выражения с модификатором /u не работают с невалидным UTF-8:
        // возвращаем текст одним токеном, чтобы не потерять данные
        if (!mb_check_encoding($input, 'UTF-8')) {
            return $this->unprocessedTokens($input, 'invalid-utf8', $tokenizationStart);
        }

        $originalInput = $input;

        $input = trim($input, " \n\r\v\0");

        // Запоминаем стиль переводов строк входного текста
        $this->eol = preg_match('/\r\n|\r|\n/', $input, $eolMatches) ? $eolMatches[0] : "\n";

        // Сначала выделяем специальные теги (script, style, pre) с их содержимым
        $preservedInput = $this->preserveSpecialTags($input);

        // Ошибка PCRE (например, исчерпан лимит): без выделенных специальных тегов
        // их содержимое было бы типографировано, поэтому возвращаем текст без обработки
        if ($preservedInput === null) {
            return $this->unprocessedTokens($originalInput, 'pcre-error', $tokenizationStart);
        }

        $input = $preservedInput;

        // Заменяем все неразрывные пробелы на обычные (потом будет не оптимально)
        $input = str_replace([' ', ' ', '&nbsp;'], ' ', $input);

        // Заменяем все неразрывные дефисы на обычные
        $input = str_replace([HtmlEntityHelper::decodeEntity('&#8209;')], '-', $input);

        $input = HtmlHelper::replaceNewlinesInTags($input, $this->newlineMarker);

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

        $processedInput = self::normalizeLineWhitespace($input, $this->newlineMarker);

        $processedInput = HtmlHelper::restoreNewlinesInTags($processedInput, $this->eol, $this->newlineMarker);

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
     * @param string $newlineMarker Метка переносов строк внутри тегов
     * @return string
     */
    private static function normalizeLineWhitespace(string $line, string $newlineMarker): string
    {
        if (HtmlHelper::isStringContainsMultilineOpeningTag($line, $newlineMarker)) {
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

        // Заменяем все пробелы и табуляции в оставшейся части на один пробел,
        // кроме пробелов внутри HTML-тегов (значения атрибутов не изменяются)
        $restOfLine = trim($restOfLine);
        $restOfLine = preg_replace_callback(
            '/(<\/?[a-z][a-z0-9:-]*+(?:"[^"]*+"|\'[^\']*+\'|[^\'">])*+>)|\s+/iu',
            fn(array $m) => ($m[1] ?? '') !== '' ? $m[1] : ' ',
            $restOfLine
        ) ?? $restOfLine;

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
            $isEmojiPropertySupported = version_compare(
                strtok(PCRE_VERSION, ' '),
                self::EMOJI_PROPERTY_MIN_PCRE_VERSION,
                '>='
            );

            self::$anchoredPatterns = array_map(function (array $pattern) use ($isEmojiPropertySupported) {
                if (isset($pattern['fallback'])) {
                    if (!$isEmojiPropertySupported) {
                        $pattern['pattern'] = $pattern['fallback'];
                    }
                    unset($pattern['fallback']);
                }

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
     * Возвращает весь текст одним токеном, который не изменяется правилами
     *
     * @param string $input Исходный текст
     * @param string $reason Причина отказа от обработки (имя токена)
     * @param float $tokenizationStart Время начала токенизации
     * @return array[]
     */
    private function unprocessedTokens(string $input, string $reason, float $tokenizationStart): array
    {
        $this->metrics['countOfLines'] = 1;
        $this->metrics['countOfTokens'] = 1;
        $this->metrics['tokenizationTime'] = microtime(true) - $tokenizationStart;

        return [
            [
                'type' => 'char',
                'name' => $reason,
                'value' => $input,
            ],
        ];
    }

    /**
     * Заменяет специальные теги на временные метки в тексте.
     *
     * Содержимое тегов сканируется possessive-циклом до первого закрывающего тега:
     * так время линейно, а длинное содержимое не упирается в pcre.backtrack_limit,
     * как это было с ленивым [\s\S]*?.
     *
     * @param string $input
     * @return string|null Null при ошибке PCRE
     */
    private function preserveSpecialTags(string $input): ?string
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
            '/<!DOCTYPE\s[^>]++>/i' => function ($m) use ($createPlaceholder) {
                return $createPlaceholder($m, 'doctype');
            },
            '/<!--(?:[^-]++|-(?!->))*+-->/' => function ($m) use ($createPlaceholder) {
                return $createPlaceholder($m, 'comment');
            },
            '/<\?(?:php|=)?\b(?:[^?]++|\?(?!>))*+\?>/' => function ($m) use ($createPlaceholder) {
                return $createPlaceholder($m, 'php');
            },
            '/<script\b[^>]*+>(?:[^<]++|<(?!\/script>))*+<\/script>/i' => function ($m) use ($createPlaceholder) {
                return $createPlaceholder($m, 'script');
            },
            '/<style\b[^>]*+>(?:[^<]++|<(?!\/style>))*+<\/style>/i' => function ($m) use ($createPlaceholder) {
                return $createPlaceholder($m, 'style');
            },
            '/<pre\b[^>]*+>(?:[^<]++|<(?!\/pre>))*+<\/pre>/i' => function ($m) use ($createPlaceholder) {
                return $createPlaceholder($m, 'pre');
            },
            // Код, ввод с клавиатуры, вывод программ и поля ввода — не типографируются
            '/<(textarea|code|kbd|samp)(?=[\s>\/])[^>]*+>(?:[^<]++|<(?!\/\1\s*+>))*+<\/\1\s*+>/i' => function ($m) use ($createPlaceholder) {
                return $createPlaceholder($m, strtolower($m[1]));
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
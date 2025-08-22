<?php

namespace Yepteam\Typograph\Helpers;

class TokenHelper
{
    /**
     * @var array|string[] Маркеры конца предложения
     */
    public static array $end_of_sentence_marks = [
        '.',
        '?',
        '!',
        '…',
    ];

    public static array $right_adjacent_marks = [
        '.',
        '?',
        '!',
        '…',
        ',',
        ':',
        ';',
        ')',
        ']',
        '}',
    ];

    public static array $prev_token_seek_ignore_symbols = [
        '*',
        '\\',
        '/',
        ')',
        ']',
        '}',
        '»',
        '"'
    ];

    /**
     * Находит индекс ближайшего предыдущего токена, который не является тегом
     * @param array $tokens
     * @param int $currentIndex
     * @param array|string $type Если указано, вернет false, если следующий токен не этого типа
     * @param callable|null $ignoreCallback
     * Произвольный метод для игнорирования токенов.
     * Если не указан - пропускаются все теги.
     * @return int|false
     */
    public static function findPrevToken(
        array        $tokens,
        int          $currentIndex,
        array|string $type = [],
        ?callable    $ignoreCallback = null
    ): int|false
    {
        if (is_string($type)) {
            $type = [$type];
        }

        for ($prevIdx = $currentIndex - 1; $prevIdx >= 0; $prevIdx--) {
            $token = $tokens[$prevIdx];

            // Всегда пропускать 'empty' токены
            if ($token['type'] === 'empty') {
                continue;
            }

            $shouldIgnore = $ignoreCallback !== null
                ? $ignoreCallback($token)
                : (!in_array('tag', $type) && $token['type'] === 'tag');

            if ($shouldIgnore) {
                continue;
            }

            if (!empty($type) && !in_array($token['type'], $type)) {
                return false;
            }

            return $prevIdx;
        }

        return false;
    }

    /**
     * Находит индекс ближайшего следующего токена, который не является тегом или пустым
     * @param array $tokens
     * @param int $currentIndex
     * @param array|string $type Если указано, вернет false, если следующий токен не этого типа
     * @param callable|null $ignoreCallback
     * Произвольный метод для игнорирования токенов.
     * Если не указан - пропускаются все теги.
     * @return int|false
     */
    public static function findNextToken(
        array        $tokens,
        int          $currentIndex,
        array|string $type = [],
        ?callable    $ignoreCallback = null
    ): int|false
    {
        if (is_string($type)) {
            $type = [$type];
        }

        for ($nextIdx = $currentIndex + 1; $nextIdx < count($tokens); $nextIdx++) {
            $token = $tokens[$nextIdx];

            // Всегда пропускать 'empty' токены
            if ($token['type'] === 'empty') {
                continue;
            }

            $shouldIgnore = $ignoreCallback !== null
                ? $ignoreCallback($token)
                : (!in_array('tag', $type) && $token['type'] === 'tag');

            if ($shouldIgnore) {
                continue;
            }

            if (!empty($type) && !in_array($token['type'], $type)) {
                return false;
            }

            return $nextIdx;
        }

        return false;
    }

    /**
     * Проверяет, находится ли токен в начале предложения (игнорируя теги перед ним)
     */
    public static function isAtStartOfSentence(array $tokens, int $index): bool
    {
        $prev_tag_index = TokenHelper::findPrevToken($tokens, $index, 'tag');
        if ($prev_tag_index !== false && in_array($tokens[$prev_tag_index]['name'], HtmlHelper::$new_line_tags)) {
            return true;
        }

        $prev_index = TokenHelper::findPrevToken($tokens, $index);

        // Перед токеном нет ничего (кроме тегов)
        if ($prev_index === false) {
            return true;
        }

        $space_index = TokenHelper::findPrevToken($tokens, $index, 'space');
        $new_line_index = TokenHelper::findPrevToken($tokens, $index, 'new-line');

        // Перед токеном нет пробела?
        if ($space_index === false) {

            // Не пробел и не перенос строки
            if ($new_line_index === false) {
                return false;
            }

            return true;
        }

        // Проверяем токен перед пробелом
        $prev_index = TokenHelper::findPrevToken($tokens, $space_index);
        if ($prev_index === false) {
            return true;
        }

        // Значение перед пробелом
        $prev_value = $tokens[$prev_index]['value'] ?? '';

        // Перед пробелом признак конца предложения?
        if (in_array($prev_value, TokenHelper::$end_of_sentence_marks)) {
            // Токен является началом предложения
            return true;
        }

        return false;
    }

    /**
     * Ищет предыдущий токен, игнорируя инициалы, пробелы и неразрывные пробелы
     */
    public static function findPrevIgnoringTokens(array $tokens, int $index, array $ignoreTokens = ['tag']): int|false
    {
        $i = $index;
        while (($i = TokenHelper::findPrevToken($tokens, $i)) !== false) {
            if (in_array($tokens[$i]['type'], $ignoreTokens)) {
                continue;
            }
            return $i;
        }

        return false;
    }

    /**
     * Ищет следующий токен, игнорируя инициалы, пробелы и неразрывные пробелы
     */
    public static function findNextIgnoringTokens(array $tokens, int $index, array $ignoreTokens = ['tag']): int|false
    {
        $i = $index;
        while (($i = TokenHelper::findNextToken($tokens, $i)) !== false) {
            if (in_array($tokens[$i]['type'], $ignoreTokens)) {
                continue;
            }
            return $i;
        }

        return false;
    }

    /**
     * Находится ли токен между пробелами
     *
     * @param array $tokens
     * @param int $index
     * @return bool
     */
    public static function isSurroundedByNumbers(array $tokens, int $index): bool
    {
        $prevIdx = TokenHelper::findPrevToken($tokens, $index);
        $nextIdx = TokenHelper::findNextToken($tokens, $index);

        return (
            $prevIdx !== false &&
            $nextIdx !== false &&
            $tokens[$prevIdx]['type'] === 'number' &&
            $tokens[$nextIdx]['type'] === 'number'
        );
    }

    /**
     * Находится ли токен в начале любой строки
     *
     * @param array $tokens
     * @param int $index
     * @return bool
     */
    public static function isAtStartOfLineWithSpace(array $tokens, int $index): bool
    {
        // Находим первый непробельный токен после текущего
        $nextNonTagIdx = TokenHelper::findNextToken($tokens, $index);

        // Проверяем, что после дефиса идет пробел
        $hasSpaceAfter = $nextNonTagIdx !== false && $tokens[$nextNonTagIdx]['type'] === 'space';
        if (!$hasSpaceAfter) {
            return false;
        }

        // Токен находится в начале массива (первый в тексте)
        if ($index === 0) {
            return true;
        }

        // Проверяем все предыдущие токены - если среди них есть только теги и/или new-line
        $prevIdx = $index - 1;
        while ($prevIdx >= 0) {
            $token = $tokens[$prevIdx];
            if ($token['type'] === 'new-line') {
                return true;
            }
            if ($token['type'] !== 'tag' && $token['type'] !== 'empty') {
                break;
            }
            $prevIdx--;
        }

        // Если дошли до начала массива (все предыдущие токены были тегами)
        return $prevIdx < 0;
    }

    /**
     * Находится ли токен между пробелами
     *
     * @param array $tokens
     * @param int $index
     * @return bool
     */
    public static function isSurroundedBySpaces(array $tokens, int $index): bool
    {
        $prev_index = TokenHelper::findPrevToken($tokens, $index);
        $next_index = TokenHelper::findNextToken($tokens, $index);

        $spaces = ['space', 'nbsp'];

        return (
            $prev_index !== false &&
            $next_index !== false &&
            in_array($tokens[$prev_index]['type'], $spaces) &&
            in_array($tokens[$next_index]['type'], $spaces)
        );
    }

    /**
     * Добавляет правило к токену для отладки
     *
     * @param array $token Токен
     * @param string $rule Название класса и номер строки
     * @param bool $applied Флаг успешного применения правила
     * @return void
     */
    public static function logRule(array &$token, string $rule, bool $applied = true): void
    {
        if (!defined('TYPOGRAPH_DEBUG') || !constant('TYPOGRAPH_DEBUG')) {
            return;
        }

        if (!isset($token['rules'])) {
            $token['rules'] = [];
        }

        $token['rules'][$rule] = $applied;
    }

}
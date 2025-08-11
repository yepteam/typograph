<?php

namespace Yepteam\Typograph\Rules\Nbsp;

use Yepteam\Typograph\Helpers\StringHelper;
use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Rules\Formatting\HtmlEntities;

/**
 * Замена пробела до или после числа на неразрывный пробел
 */
class Number
{
    public static function apply(int $index, array &$tokens): void
    {
        // Применимо только для числа
        if ($tokens[$index]['type'] !== 'number') {
            return;
        }

        self::applyBefore($index, $tokens);

        self::applyAfter($index, $tokens);
    }

    public static function applyBefore(int $index, array &$tokens): void
    {
        // Предыдущий токен должен быть пробелом
        $space_index = TokenHelper::findPrevToken($tokens, $index, 'space');
        if ($space_index === false) {
            return;
        }

        $before_space_index = TokenHelper::findPrevToken($tokens, $space_index);
        if ($before_space_index === false) {
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        // Если перед пробелом entity
        if ($tokens[$before_space_index]['type'] === 'entity') {
            // То nbsp слева не ставим
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        // Если перед пробелом строка заканчивается:
        // - знаком «плюс»
        // - знаком «минус»
        // - знаком «равно»
        // - звездочкой
        // - hyphen/ndash/mdash
        if (preg_match('/[+−=*\-–—]$/u', $tokens[$before_space_index]['value']) === 1) {
            // То nbsp слева не ставим
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        $next_index = TokenHelper::findNextIgnoringTokens($tokens, $index, ['tag', 'space']);

        // После числа нет токенов или после числа есть признак конца строки
        if ($next_index === false || $tokens[$next_index]['type'] === 'new-line' ||
            in_array($tokens[$next_index]['value'], TokenHelper::$end_of_sentence_marks)) {

            // Если значение числа состоит из менее 3 символов
            if (mb_strlen($tokens[$index]['value']) < 3) {
                // Заменяем токен space на nbsp
                $tokens[$space_index] = [
                    'type' => 'nbsp',
                    'value' => HtmlEntities::decodeEntity('&nbsp;'),
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
                return;
            }
        }

        // Если справа слово…
        if ($tokens[$next_index]['type'] === 'word') {

            // Если слово в верхнем или в нижнем регистре
            if (StringHelper::isLowerCase($tokens[$next_index]['value']) || StringHelper::isUpperCase($tokens[$next_index]['value'])) {
                // То nbsp слева не ставим
                $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
                return;
            }
        }

        $left_word_index = TokenHelper::findPrevIgnoringTokens($tokens, $index, ['tag', 'space']);
        $right_word_index = TokenHelper::findNextIgnoringTokens($tokens, $index, ['tag', 'space']);

        // Если перед пробелом слово
        if ($left_word_index !== false && $tokens[$left_word_index]['type'] === 'word') {

            // Если перед пробелом слово с большой буквы (остальные прописные)
            if (StringHelper::isLowerCaseExceptFirst($tokens[$left_word_index]['value'])) {

                if ($right_word_index === false) {
                    // Заменяем токен space на nbsp
                    $tokens[$space_index] = [
                        'type' => 'nbsp',
                        'value' => HtmlEntities::decodeEntity('&nbsp;'),
                        'rule' => __CLASS__ . ':' . __LINE__,
                    ];
                    return;
                }

                if ($tokens[$right_word_index]['type'] === 'word' && StringHelper::isUcFirstValue($tokens[$right_word_index]['value'])) {
                    // Заменяем токен space на nbsp
                    $tokens[$space_index] = [
                        'type' => 'nbsp',
                        'value' => HtmlEntities::decodeEntity('&nbsp;'),
                        'rule' => __CLASS__ . ':' . __LINE__,
                    ];
                    return;
                }
            }
        }

        // Если перед пробелом точка
        $dot_index = TokenHelper::findPrevToken($tokens, $space_index, 'dot');
        if ($dot_index !== false) {
            $left_word_index = TokenHelper::findPrevToken($tokens, $dot_index);

            // Перед точкой должно быть слово
            if ($left_word_index === false) {
                $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
                return;
            }

            // Слово должно заканчиваться строчной буквой
            if (!preg_match('/^\p{Ll}/u', $tokens[$left_word_index]['value'])) {
                $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
                return;
            }

            $next_dash_index = TokenHelper::findNextToken($tokens, $index, ['hyphen', 'nbhy', 'ndash', 'mdash']);
            if ($next_dash_index !== false) {
                $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
                return;
            }

            // Заменяем токен space на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => HtmlEntities::decodeEntity('&nbsp;'),
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // Если перед пробелом символ
        $char_index = TokenHelper::findPrevToken($tokens, $space_index, 'char');
        if ($char_index !== false) {

            // Если перед пробелом символ номера
            if ($tokens[$char_index]['value'] === '№') {
                // Заменяем токен space на nbsp
                $tokens[$space_index] = [
                    'type' => 'nbsp',
                    'value' => HtmlEntities::decodeEntity('&nbsp;'),
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
                return;
            }
        }

        $prev_index = TokenHelper::findPrevToken($tokens, $space_index);
        if ($prev_index === false) {
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        $prev_value = $tokens[$prev_index]['value'];

        // Перед числом знак валюты?
        if (in_array($prev_value, StringHelper::$currency_symbols)) {
            // Заменяем токен space на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => HtmlEntities::decodeEntity('&nbsp;'),
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
        }

        // Перед числом число до 3 знаков?
        if (preg_match('/^\d{1,3}$/u', $prev_value)) {
            // Заменяем токен space на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => HtmlEntities::decodeEntity('&nbsp;'),
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
        }

        $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
    }

    public static function applyAfter(int $index, array &$tokens): void
    {
        // После числа должен быть пробел
        $space_index = TokenHelper::findNextToken($tokens, $index, 'space');
        if ($space_index === false) {
            return;
        }

        $prev_index = TokenHelper::findPrevToken($tokens, $index);

        if ($prev_index !== false) {
            // Перед числом нет одного из:
            // - пробел
            // - неразрывный пробел
            // - дефис
            // - пунктуация
            // - одиночный символ
            if (!in_array($tokens[$prev_index]['type'], ['space', 'nbsp', 'hyphen', 'nbhy', 'punctuation', 'char'])) {
                // Ничего не делаем
                $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
                return;
            }
        }

        $next_index = TokenHelper::findNextToken($tokens, $space_index);
        if ($next_index === false) {
            // Ничего не делаем
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        // Если токен после пробела является одним из:
        // - дефис
        // - неразрывный дефис
        // - ndash
        // - mdash
        // - entity
        if (in_array($tokens[$next_index]['type'], ['hyphen', 'nbhy', 'ndash', 'mdash', 'entity'])) {
            // Ничего не делаем
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        // Слово после числа не должно быть похоже на имя.
        if ($tokens[$next_index]['type'] === 'word') {
            if (mb_strlen($tokens[$next_index]['value']) < 3) {
                // Заменяем токен space на nbsp
                $tokens[$space_index] = [
                    'type' => 'nbsp',
                    'value' => HtmlEntities::decodeEntity('&nbsp;'),
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
            }

            // Все буквы кроме первой маленькие?
            if (StringHelper::isLowerCaseExceptFirst($tokens[$next_index]['value'])) {
                // Ничего не делаем
                $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
                return;
            }
        }

        $next_value = $tokens[$next_index]['value'] ?? '';

        $next_value_length = mb_strlen($next_value);

        // После числа текст более 4 символов
        if ($next_value_length > 4) {

            $prev_index = TokenHelper::findPrevToken($tokens, $index);

            if ($prev_index === false) {
                // Заменяем токен space на nbsp
                $tokens[$space_index] = [
                    'type' => 'nbsp',
                    'value' => HtmlEntities::decodeEntity('&nbsp;'),
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
            }

            // Перед числом токен char?
            if ($tokens[$prev_index]['type'] === 'char') {
                // Заменяем токен space на nbsp
                $tokens[$space_index] = [
                    'type' => 'nbsp',
                    'value' => HtmlEntities::decodeEntity('&nbsp;'),
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
                return;
            }

            // Перед числом дефис?
            if (in_array($tokens[$prev_index]['type'], ['hyphen', 'nbhy'])) {
                // Ничего не делаем
                $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
                return;
            }

            // Перед числом не nbsp и длина числа больше 2 символов?
            if ($tokens[$prev_index]['type'] !== 'nbsp') {
                // Заменяем токен space на nbsp
                $tokens[$space_index] = [
                    'type' => 'nbsp',
                    'value' => HtmlEntities::decodeEntity('&nbsp;'),
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
                return;
            }

            if (mb_strlen($tokens[$index]['value']) <= 2) {
                // Заменяем токен space на nbsp
                $tokens[$space_index] = [
                    'type' => 'nbsp',
                    'value' => HtmlEntities::decodeEntity('&nbsp;'),
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
                return;
            }

            // Ничего не делаем
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        // Флаг наличия nbsp перед числом
        $has_nbsp_before_number = $tokens[$prev_index]['type'] === 'nbsp';

        // После числа текст длиной 4 символа
        if ($next_value_length === 4) {
            $next_index = TokenHelper::findNextToken($tokens, $next_index);

            // После текста ничего нет?
            if ($next_index === false) {
                // Перед числом не было nbsp?
                if (!$has_nbsp_before_number) {
                    // Заменяем токен space на nbsp
                    $tokens[$space_index] = [
                        'type' => 'nbsp',
                        'value' => HtmlEntities::decodeEntity('&nbsp;'),
                        'rule' => __CLASS__ . ':' . __LINE__,
                    ];
                }
                return;
            }

            // После текста перенос строки?
            if ($tokens[$next_index]['type'] === 'new-line') {
                // Перед числом не было nbsp?
                if (!$has_nbsp_before_number) {
                    // Заменяем токен space на nbsp
                    $tokens[$space_index] = [
                        'type' => 'nbsp',
                        'value' => HtmlEntities::decodeEntity('&nbsp;'),
                        'rule' => __CLASS__ . ':' . __LINE__,
                    ];
                }
                return;
            }

            $next_value = $tokens[$next_index]['value'] ?? '';

            // После текста пунктуация?
            if (in_array($next_value, ['.', ',', ':', ';', '?', ')', ']', '>', '/', '"'])) {
                // Заменяем токен space на nbsp
                $tokens[$space_index] = [
                    'type' => 'nbsp',
                    'value' => HtmlEntities::decodeEntity('&nbsp;'),
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
                return;
            }

            // После текста пробел?
            if ($tokens[$next_index]['type'] === 'space') {
                // Заменяем токен space на nbsp
                $tokens[$space_index] = [
                    'type' => 'nbsp',
                    'value' => HtmlEntities::decodeEntity('&nbsp;'),
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
            }
        }

        // Следующий после пробела токен трехзначное число?
        if (preg_match('/^\d{3}$/u', $next_value)) {

            // Заменяем токен space на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => HtmlEntities::decodeEntity('&nbsp;'),
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // После числа текст длиной 3 символа
        if ($next_value_length === 3) {
            $next_index = TokenHelper::findNextToken($tokens, $next_index);
            $next_value = $tokens[$next_index]['value'] ?? '';

            // После текста ничего нет
            if ($next_index === false) {
                // Заменяем токен space на nbsp
                $tokens[$space_index] = [
                    'type' => 'nbsp',
                    'value' => HtmlEntities::decodeEntity('&nbsp;'),
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
                return;
            }

            // После текста перенос строки?
            if ($tokens[$next_index]['type'] === 'new-line') {
                // Заменяем токен space на nbsp
                $tokens[$space_index] = [
                    'type' => 'nbsp',
                    'value' => HtmlEntities::decodeEntity('&nbsp;'),
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
                return;
            }

            // После текста примыкающий знак препинания
            if (in_array($next_value, TokenHelper::$right_adjacent_marks)) {
                // Заменяем токен space на nbsp
                $tokens[$space_index] = [
                    'type' => 'nbsp',
                    'value' => HtmlEntities::decodeEntity('&nbsp;'),
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
                return;
            }

            // После текста пробел
            if ($tokens[$next_index]['type'] === 'space') {
                // Заменяем токен space на nbsp
                $tokens[$space_index] = [
                    'type' => 'nbsp',
                    'value' => HtmlEntities::decodeEntity('&nbsp;'),
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
            }

            // После текста нет точки
            if ($next_value !== '.') {

                $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
                return;
            }
        }

        // После числа следует знак валюты?
        if (in_array($next_value, StringHelper::$currency_symbols)) {

            // Заменяем токен space на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => HtmlEntities::decodeEntity('&nbsp;'),
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // После числа следует определенный знак?
        if (in_array($next_value, ['%', '&', '©', '°'])) {
            // Заменяем токен space на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => HtmlEntities::decodeEntity('&nbsp;'),
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // Оставляем пробел
        $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
    }
}

<?php

namespace Yepteam\Typograph\Rules\Nbsp;

use Yepteam\Typograph\Helpers\StringHelper;
use Yepteam\Typograph\Helpers\TokenHelper;

class Initial
{
    public static function apply(int $index, array &$tokens): void
    {
        if ($tokens[$index]['type'] !== 'initial') {
            return;
        }

        // Обрабатываем пробел перед текущим инициалом
        self::processSpaceBeforeInitial($index, $tokens);

        // Обрабатываем пробел после текущего инициала
        self::processSpaceAfterInitial($index, $tokens);
    }

    /**
     * Обрабатывает пробел перед инициалом
     */
    private static function processSpaceBeforeInitial(int $index, array &$tokens): void
    {
        // Находим пробел слева без учета тегов
        $space_index = TokenHelper::findPrevToken($tokens, $index, 'space');
        if ($space_index === false) {
            // Если слева нет пробела, выходим
            return;
        }

        $before_space_index = TokenHelper::findPrevToken($tokens, $space_index);
        if ($before_space_index === false) {
            // Если перед пробелом ничего нет, выходим
            return;
        }

        // Если перед пробелом значение, заканчивающаяся цифрой
        if (StringHelper::isEndsWithNumber($tokens[$before_space_index]['value'])) {
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // Если перед пробелом нет слова
        if ($tokens[$before_space_index]['type'] !== 'word') {
            return;
        }

        // Если перед пробелом нет слова с большой буквы
        if (!StringHelper::isUcFirstValue($tokens[$before_space_index]['value'])) {
            return;
        }

        // Ищем токен после инициалов справа (игнорируя инициалы и пробелы)
        $right_token = TokenHelper::findNextIgnoringTokens($tokens, $index, ['initial', 'tag', 'space', 'nbsp']);

        if ($right_token === false) {
            // Заменяем на неразрывный пробел
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
        }

        // Если токен справа является словом с большой буквы
        if (StringHelper::isUcFirstValue($tokens[$right_token]['value'])) {
            // Ничего не делаем
            return;
        }

        // Заменяем на неразрывный пробел
        $tokens[$space_index] = [
            'type' => 'nbsp',
            'value' => ' ',
            'rule' => __CLASS__ . ':' . __LINE__,
        ];
    }

    /**
     * Обрабатывает пробел после инициала
     */
    private static function processSpaceAfterInitial(int $index, array &$tokens): void
    {
        $space_index = TokenHelper::findNextToken($tokens, $index, 'space');
        if ($space_index === false) {
            // Если справа нет пробела, выходим
            return;
        }

        $after_space_index = TokenHelper::findNextToken($tokens, $space_index);
        if ($after_space_index === false) {
            // Если после пробела ничего нет, выходим
            return;
        }

        // Всегда заменяем пробел между инициалами на неразрывный
        if ($tokens[$after_space_index]['type'] === 'initial') {
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // Если после пробела слово с большой буквы
        if (StringHelper::isUcFirstValue($tokens[$after_space_index]['value'])) {

            // Проверяем ближайший токен слева (игнорируя инициалы и пробелы)
            $left_token = TokenHelper::findPrevIgnoringTokens($tokens, $index, ['initial', 'tag', 'space', 'nbsp']);

            // Слева от инициалов нет токенов - препятствий для замены нет
            if ($left_token === false) {
                // Заменяем пробел на неразрывный
                $tokens[$space_index] = [
                    'type' => 'nbsp',
                    'value' => ' ',
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
            }

            // Является ли токен слева словом с большой буквы
            $isCapitalWord = $tokens[$left_token]['type'] === 'word' && StringHelper::isUcFirstValue($tokens[$left_token]['value']);
            if ($isCapitalWord) {
                return;
            }

            // Заканчивается ли токен слева цифрой
            $isNumber = StringHelper::isEndsWithNumber($tokens[$left_token]['value']);
            if ($isNumber) {
                return;
            }

            // Заменяем пробел на неразрывный
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
        }
    }

}
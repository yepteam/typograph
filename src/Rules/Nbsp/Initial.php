<?php

namespace Yepteam\Typograph\Rules\Nbsp;

use Yepteam\Typograph\Helpers\StringHelper;
use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Rules\BaseRule;

final class Initial extends BaseRule
{
    public static function apply(int $index, array &$tokens, array $options): void
    {
        if ($tokens[$index]['type'] !== 'initial') {
            return;
        }

        // Обрабатываем пробел перед текущим инициалом
        self::applyBefore($index, $tokens, $options);

        // Обрабатываем пробел после текущего инициала
        self::applyAfter($index, $tokens, $options);
    }

    /**
     * Обрабатывает пробел перед инициалом
     */
    private static function applyBefore(int $index, array &$tokens, array $options): void
    {
        // Находим пробел слева без учета тегов
        $space_index = TokenHelper::findPrevToken($tokens, $index, 'space');
        if ($space_index === false) {
            // Если слева нет пробела, выходим
            return;
        }

        $before_space_index = TokenHelper::findPrevToken($tokens, $space_index);

        // Если перед пробелом ничего нет, выходим
        if ($before_space_index === false) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        // Если перед пробелом значение, заканчивающаяся цифрой
        if (StringHelper::isEndsWithNumber($tokens[$before_space_index]['value'])) {
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Если перед пробелом нет слова
        if ($tokens[$before_space_index]['type'] !== 'word') {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        // Если перед пробелом нет слова с большой буквы
        if (!StringHelper::isUcFirstValue($tokens[$before_space_index]['value'])) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        // Ищем токен после инициалов справа (игнорируя инициалы и пробелы)
        $right_token = TokenHelper::findNextIgnoringTokens($tokens, $index, ['initial', 'tag', 'space', 'nbsp']);
        if ($right_token === false) {
            // Заменяем на неразрывный пробел
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Если токен справа является словом с большой буквы
        if (StringHelper::isUcFirstValue($tokens[$right_token]['value'])) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        // Заменяем на неразрывный пробел
        $tokens[$space_index] = [
            'type' => 'nbsp',
            'value' => ' ',
        ];
        !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
    }

    /**
     * Обрабатывает пробел после инициала
     */
    private static function applyAfter(int $index, array &$tokens, array $options): void
    {
        $space_index = TokenHelper::findNextToken($tokens, $index, 'space');
        if ($space_index === false) {
            // Если справа нет пробела, выходим
            return;
        }

        // Если после пробела ничего нет, выходим
        $after_space_index = TokenHelper::findNextToken($tokens, $space_index);
        if ($after_space_index === false) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        // Всегда заменяем пробел между инициалами на неразрывный
        if ($tokens[$after_space_index]['type'] === 'initial') {
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
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
                ];
                !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
                return;
            }

            // Является ли токен слева словом с большой буквы
            $isCapitalWord = $tokens[$left_token]['type'] === 'word' && StringHelper::isUcFirstValue($tokens[$left_token]['value']);
            if ($isCapitalWord) {
                !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
                return;
            }

            // Заканчивается ли токен слева цифрой
            $isNumber = StringHelper::isEndsWithNumber($tokens[$left_token]['value']);
            if ($isNumber) {
                !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
                return;
            }

            // Заменяем пробел на неразрывный
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
        }
    }

}
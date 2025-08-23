<?php

namespace Yepteam\Typograph\Rules\Nbsp;

use Yepteam\Typograph\Helpers\HtmlHelper;
use Yepteam\Typograph\Helpers\StringHelper;
use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Rules\BaseRule;

/**
 * Замена пробела перед mdash на nbsp
 */
class Mdash extends BaseRule
{
    public static function apply(int $index, array &$tokens, array $options): void
    {
        if (!in_array($tokens[$index]['type'], ['mdash', 'hyphen'])) {
            return;
        }

        // Обрабатывает пробел перед символом черты
        self::applyBefore($index, $tokens, $options);

        // Обрабатывает пробел после символа черты
        self::applyAfter($index, $tokens, $options);
    }

    public static function applyBefore(int $index, array &$tokens, array $options): void
    {
        // Ищем предыдущий индекс без учета тегов
        $space_index = TokenHelper::findPrevToken($tokens, $index, 'space');

        // Предыдущий токен должен быть пробелом
        if ($space_index === false) {
            return;
        }

        $after_space_tag_index = TokenHelper::findPrevToken($tokens, $index, 'tag');
        $before_space_tag_index = TokenHelper::findPrevToken($tokens, $space_index, 'tag');

        if ($after_space_tag_index !== false && in_array($tokens[$after_space_tag_index]['name'], HtmlHelper::$new_line_tags)) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }
        if ($before_space_tag_index !== false && in_array($tokens[$before_space_tag_index]['name'], HtmlHelper::$new_line_tags)) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        // Перед пробелом ищем токен, игнорируя некоторые символы
        $prev_index = TokenHelper::findPrevToken($tokens, $space_index, [], function ($token) {

            // Не учитываем теги, но шорткоды пропускаем
            if ($token['type'] === 'tag' && $token['name'] !== 'shortcode') {
                return true;
            }

            // Не учитываем некоторые символы
            if (in_array($token['value'], TokenHelper::$prev_token_seek_ignore_symbols)) {
                return true;
            }

            return false;
        });

        if ($prev_index === false) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        $prev_value = $tokens[$prev_index]['value'] ?? '';

        // Перед пробелом значение, оканчивающееся символом доллара?
        if (preg_match('/\$$/u', $prev_value) === 1) {
            // Заменяем пробел на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Перед пробелом значение, оканчивающееся цифрой или буквой?
        if (StringHelper::isEndsWithAlphaNumeric($prev_value)) {
            // Заменяем пробел на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Перед пробелом запятая?
        if ($prev_value === ',') {
            // Заменяем пробел на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Перед пробелом шорткод?
        if ($tokens[$prev_index]['type'] === 'tag' && $tokens[$prev_index]['name'] === 'shortcode') {
            // Заменяем пробел на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
            return;
        }

        !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
    }

    public static function applyAfter(int $index, array &$tokens, array $options): void
    {
        // Токен mdash должен быть в начале строки
        if (!TokenHelper::isAtStartOfSentence($tokens, $index)) {
            return;
        }

        // Находим индекс следующего токена без учета тегов
        $space_index = TokenHelper::findNextToken($tokens, $index, 'space');

        // Следующий токен должен быть пробелом
        if ($space_index === false) {
            return;
        }

        // Заменяем пробел на неразрывный
        $tokens[$space_index] = [
            'type' => 'nbsp',
            'value' => ' ',
        ];
        !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
    }
}
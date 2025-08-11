<?php

namespace Yepteam\Typograph\Rules\Nbsp;

use Yepteam\Typograph\Helpers\HtmlHelper;
use Yepteam\Typograph\Helpers\StringHelper;
use Yepteam\Typograph\Helpers\TokenHelper;

/**
 * Замена пробела перед mdash на nbsp
 */
class Mdash
{
    public static function apply(int $index, array &$tokens): void
    {
        if (!in_array($tokens[$index]['type'], ['mdash', 'hyphen'])) {
            return;
        }

        self::applyBefore($index, $tokens);

        self::applyAfter($index, $tokens);
    }

    public static function applyBefore(int $index, array &$tokens): void
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
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }
        if ($before_space_tag_index !== false && in_array($tokens[$before_space_tag_index]['name'], HtmlHelper::$new_line_tags)) {
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        // Перед пробелом ищем токен, игнорируя некоторые символы
        $prev_index = TokenHelper::findPrevToken($tokens, $space_index, [], function ($token) {
            return $token['type'] === 'tag' ||
                in_array($token['value'], [
                    '*',
                    '\\',
                    '/',
                    ')',
                    ']',
                    '}',
                    '»',
                    '"',
                ]);
        });

        if ($prev_index === false) {
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        $prev_value = $tokens[$prev_index]['value'] ?? '';

        // Перед пробелом значение, оканчивающееся символом доллара?
        if (preg_match('/\$$/u', $prev_value) === 1) {
            // Заменяем mdash на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // Перед пробелом значение, оканчивающееся цифрой или буквой?
        if (StringHelper::isEndsWithAlphaNumeric($prev_value)) {
            // Заменяем mdash на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // Перед пробелом запятая?
        if ($prev_value === ',') {
            // Заменяем mdash на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
    }

    public static function applyAfter(int $index, array &$tokens): void
    {
        // Токен mdash должен быть в начале строки
        if (!TokenHelper::isAtStartOfSentence($tokens, $index)) {
            return;
        }

        // Находим индекс следующего токена без учета тегов
        $next_index = TokenHelper::findNextToken($tokens, $index, 'space');

        // Следующий токен должен быть пробелом
        if ($next_index === false) {
            return;
        }

        // Заменяем токен space на nbsp
        $tokens[$next_index] = [
            'type' => 'nbsp',
            'value' => ' ',
            'rule' => __CLASS__ . ':' . __LINE__,
        ];
    }
}
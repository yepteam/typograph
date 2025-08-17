<?php

namespace Yepteam\Typograph\Rules\Dash;

use Yepteam\Typograph\Helpers\TokenHelper;

/**
 * Заменяет дефис на mdash (игнорируя теги)
 */
class HyphenToMdash
{
    public static function apply(int $index, array &$tokens): void
    {
        $current = $tokens[$index];

        // Применимо только к дефису
        if (!in_array($current['type'], ['hyphen', 'double-hyphen', 'nbhy'])) {
            return;
        }

        // Токен представляет собой последовательность из двух дефисов
        if ($tokens[$index]['type'] === 'double-hyphen') {
            // Замена токена на mdash
            $tokens[$index] = [
                'type' => 'mdash',
                'value' => '—',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // Дефис находится в начале строки, а после него есть пробел (игнорируя теги)
        if (TokenHelper::isAtStartOfLineWithSpace($tokens, $index)) {
            // Замена токена на mdash
            $tokens[$index] = [
                'type' => 'mdash',
                'value' => '—',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // Дефис находится между пробелами (игнорируя теги)
        if (!TokenHelper::isSurroundedBySpaces($tokens, $index)) {
            $tokens[$index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        $prev_token_index = TokenHelper::findPrevToken($tokens, $index, ['space', 'nbsp']);

        // Проверяем токен перед пробелом
        $before_space_index = TokenHelper::findPrevToken($tokens, $prev_token_index, [], function ($token) {

            // Не учитываем теги, но шорткоды пропускаем
            if ($token['type'] === 'tag' && $token['name'] !== 'shortcode') {
                return true;
            }

            // Не учитываем черточки и пробелы
            if (in_array($token['type'], ['space', 'nbsp'])) {
                return true;
            }

            // Не учитываем некоторые символы
            if (in_array($token['value'], TokenHelper::$prev_token_seek_ignore_symbols)) {
                return true;
            }

            return false;
        });

        if ($before_space_index === false) {
            $tokens[$index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        if(in_array($tokens[$before_space_index]['type'], ['hyphen', 'ndash', 'mdash', ])){
            $tokens[$index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        // Замена токена на mdash
        $tokens[$index] = [
            'type' => 'mdash',
            'value' => '—',
            'rule' => __CLASS__ . ':' . __LINE__,
        ];
    }

}
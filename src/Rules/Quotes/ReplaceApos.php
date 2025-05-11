<?php

namespace Yepteam\Typograph\Rules\Quotes;

use Yepteam\Typograph\Helpers\TokenHelper;

/**
 * Заменяет кавычки
 */
class ReplaceApos
{
    public static function apply(int $index, array &$tokens): void
    {
        $current = $tokens[$index];

        // Применимо только к токенам apos и word
        if (!in_array($current['type'], ['apos', 'word'])) {
            return;
        }

        // Замена апострофа в слове
        if ($current['type'] === 'word') {
            $tokens[$index]['value'] = str_replace("'", '’', $tokens[$index]['value']);
            $tokens[$index]['rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        // Находим токен перед апострофом
        $prev_index = TokenHelper::findPrevToken($tokens, $index);
        if ($prev_index === false) {
            $tokens[$index] = [
                'type' => 'rsquo',
                'value' => '’',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        if ($tokens[$prev_index]['type'] === 'number') {
            $tokens[$index] = [
                'type' => 'prime',
                'value' => '′',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        $tokens[$index] = [
            'type' => 'rsquo',
            'value' => '’',
            'rule' => __CLASS__ . ':' . __LINE__,
        ];
    }
}
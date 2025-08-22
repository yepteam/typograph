<?php

namespace Yepteam\Typograph\Rules\Quotes;

use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Rules\BaseRule;

/**
 * Замена апострофа
 */
class ReplaceApos extends BaseRule
{
    public static function apply(int $index, array &$tokens, array $options): void
    {
        // Применимо только к токенам apos и word
        if (!in_array($tokens[$index]['type'], ['apos', 'word'])) {
            return;
        }

        // В слове должен присутствовать апостроф
        if ($tokens[$index]['type'] === 'word' && !str_contains($tokens[$index]['value'], "'")) {
            return;
        }

        // Замена апострофа в слове
        if ($tokens[$index]['type'] === 'word') {
            $tokens[$index]['value'] = str_replace("'", '’', $tokens[$index]['value']);
            self::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Находим токен перед апострофом
        $prev_index = TokenHelper::findPrevToken($tokens, $index);

        if ($prev_index === false) {
            $tokens[$index] = [
                'type' => 'rsquo',
                'value' => '’',
            ];
            self::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
            return;
        }

        if ($tokens[$prev_index]['type'] === 'number') {
            $tokens[$index] = [
                'type' => 'prime',
                'value' => '′',
            ];
            self::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
            return;
        }

        $tokens[$index] = [
            'type' => 'rsquo',
            'value' => '’',
        ];
        self::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
    }
}
<?php

namespace Yepteam\Typograph\Rules\Quotes;

use Yepteam\Typograph\Helpers\TokenHelper;

/**
 * Замена апострофа
 */
class ReplaceApos
{
    public static function apply(int $index, array &$tokens): void
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
            TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Находим токен перед апострофом
        $prev_index = TokenHelper::findPrevToken($tokens, $index);

        if ($prev_index === false) {
            $tokens[$index] = [
                'type' => 'rsquo',
                'value' => '’',
            ];
            TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
            return;
        }

        if ($tokens[$prev_index]['type'] === 'number') {
            $tokens[$index] = [
                'type' => 'prime',
                'value' => '′',
            ];
            TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
            return;
        }

        $tokens[$index] = [
            'type' => 'rsquo',
            'value' => '’',
        ];
        TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
    }
}
<?php

namespace Yepteam\Typograph\Rules\Dash;

use Yepteam\Typograph\Helpers\TokenHelper;

/**
 * Заменяет дефис на mdash (игнорируя теги)
 */
class HyphenToMinus
{
    public static function apply(int $index, array &$tokens): void
    {
        $current = $tokens[$index];

        // Применимо только к дефису
        if ($current['type'] !== 'hyphen') {
            return;
        }

        // Дефис находится между пробелом и цифрой
        if (self::isSurroundedBySpaceAndNumber($tokens, $index)) {
            // Замена токена на mdash
            $tokens[$index] = [
                'type' => 'minus',
                'value' => '&minus;',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        $tokens[$index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
    }

    /**
     * Находится ли токен между пробелами
     *
     * @param array $tokens
     * @param int $index
     * @return bool
     */
    private static function isSurroundedBySpaceAndNumber(array $tokens, int $index): bool
    {
        $prevIdx = TokenHelper::findPrevToken($tokens, $index);
        $nextIdx = TokenHelper::findNextToken($tokens, $index);

        $spaces = ['space', 'nbsp'];

        return (
            $prevIdx !== false &&
            $nextIdx !== false &&
            in_array($tokens[$prevIdx]['type'], $spaces) &&
            $tokens[$nextIdx]['type'] === 'number'
        );
    }
}
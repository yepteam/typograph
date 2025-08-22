<?php

namespace Yepteam\Typograph\Rules\Dash;

use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Rules\BaseRule;

/**
 * Заменяет дефис на mdash (игнорируя теги)
 */
class HyphenToMinus extends BaseRule
{
    public static function apply(int $index, array &$tokens, array $options): void
    {
        // Применимо только к дефису
        if (!in_array($tokens[$index]['type'], ['hyphen', 'nbhy'])) {
            return;
        }

        // Дефис находится между пробелом и цифрой
        if (self::isSurroundedBySpaceAndNumber($tokens, $index)) {
            // Замена токена на mdash
            $tokens[$index] = [
                'type' => 'minus',
                'value' => '&minus;',
            ];
            self::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
            return;
        }

        self::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
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
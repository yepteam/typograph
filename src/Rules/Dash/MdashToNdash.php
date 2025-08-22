<?php

namespace Yepteam\Typograph\Rules\Dash;

use Yepteam\Typograph\Helpers\TokenHelper;

/**
 * Заменяет mdash на ndash
 */
class MdashToNdash
{
    public static function apply(int $index, array &$tokens): void
    {
        // Применимо только к токену mdash
        if ($tokens[$index]['type'] !== 'mdash') {
            return;
        }

        // mdash находится между числами (игнорируя теги)
        if (TokenHelper::isSurroundedByNumbers($tokens, $index)) {
            // Замена токена mdash на ndash
            $tokens[$index] = [
                'type' => 'ndash',
                'value' => '–',
            ];
            TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
            return;
        }

        TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
    }
}
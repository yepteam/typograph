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
        $current = $tokens[$index];

        // Применимо только к токену mdash
        if ($current['type'] !== 'mdash') {
            return;
        }

        // mdash находится между числами (игнорируя теги)
        if (TokenHelper::isSurroundedByNumbers($tokens, $index)) {
            // Замена токена mdash на ndash
            $tokens[$index] = [
                'type' => 'ndash',
                'value' => '–',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        $tokens[$index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
    }
}
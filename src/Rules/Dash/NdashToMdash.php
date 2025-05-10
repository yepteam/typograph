<?php

namespace Yepteam\Typograph\Rules\Dash;

use Yepteam\Typograph\Helpers\TokenHelper;

/**
 * Заменяет дефис на длинное тире (игнорируя теги)
 */
class NdashToMdash
{
    public static function apply(int $index, array &$tokens): void
    {
        $current = $tokens[$index];

        if ($current['type'] !== 'ndash') {
            return;
        }

        // Токен ndash находится в начале строки, а после него есть пробел (игнорируя теги)
        if (TokenHelper::isAtStartOfLineWithSpace($tokens, $index)) {
            // Замена токена на mdash
            $tokens[$index] = [
                'type' => 'mdash',
                'value' => '—',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // ndash находится между числами (игнорируя теги)
        if (TokenHelper::isSurroundedByNumbers($tokens, $index)) {
            $tokens[$index] = [
                'type' => 'mdash',
                'value' => '—',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // ndash находится между пробелами (игнорируя теги)
        if (TokenHelper::isSurroundedBySpaces($tokens, $index)) {
            // Замена токена на mdash
            $tokens[$index] = [
                'type' => 'mdash',
                'value' => '—',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        $tokens[$index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
    }

}
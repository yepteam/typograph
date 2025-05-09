<?php

namespace Yepteam\Typograph\Rules\Dash;

use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Tokenizer;

/**
 * Заменяет дефис на длинное тире (игнорируя теги) в двух случаях:
 * 1. Между пробелами
 * 2. В начале строки (после new-line или первый в тексте), если после него пробел
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
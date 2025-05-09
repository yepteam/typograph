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

        // Применимо только к токену apos
        if ($current['type'] !== 'apos') {
            return;
        }

        $tokens[$index] = [
            'type' => 'rsquo',
            'value' => '’',
            'rule' => __CLASS__ . ':' . __LINE__,
        ];
    }
}
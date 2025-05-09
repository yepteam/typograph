<?php

namespace Yepteam\Typograph\Rules\Nbsp;

/**
 * Заменяет все nbsp на пробел
 */
class ClearNbsp
{
    public static function apply(int $index, array &$tokens): void
    {
        // Применимо только для nbsp
        if ($tokens[$index]['type'] !== 'nbsp') {
            return;
        }

        $tokens[$index] = [
            'type' => 'space',
            'value' => ' ',
            'rule' => __CLASS__ . ':' . __LINE__,
        ];
    }
}
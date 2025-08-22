<?php

namespace Yepteam\Typograph\Rules\Punctuation;

use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Tokenizer;

/**
 * Замена многоточия на три точки
 *
 * Внимание: это спорное правило
 * @see https://habr.com/ru/articles/26384/
 * @see https://www.artlebedev.ru/kovodstvo/sections/164/
 */
class HellipToThreeDots
{
    public static function apply(int $index, array &$tokens): void
    {
        // Применимо только к символу многоточия (hellip)
        if ($tokens[$index]['type'] !== 'hellip') {
            return;
        }

        // Заменяем токен hellip на многоточие
        $tokens[$index] = [
            'type' => 'three-dots',
            'value' => '...',
        ];
        TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
    }
}
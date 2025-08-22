<?php

namespace Yepteam\Typograph\Rules\Punctuation;

use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Tokenizer;

/**
 * Замена трёх точек на многоточие
 *
 * Внимание: это спорное правило
 * @see https://habr.com/ru/articles/26384/
 * @see https://www.artlebedev.ru/kovodstvo/sections/164/
 */
class ThreeDotsToHellip
{
    public static function apply(int $index, array &$tokens): void
    {
        // Применимо только к токену «три точки подряд»
        if ($tokens[$index]['type'] !== 'three-dots') {
            return;
        }

        // Заменяем токен three-dots на hellip
        $tokens[$index] = [
            'type' => 'hellip',
            'value' => '…',
        ];
        TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
    }
}
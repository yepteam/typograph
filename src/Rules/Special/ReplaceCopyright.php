<?php

namespace Yepteam\Typograph\Rules\Special;

use Yepteam\Typograph\Helpers\TokenHelper;

/**
 * Заменяет (C) на символ ©.
 */
class ReplaceCopyright
{
    /**
     * @param int $index Индекс текущего токена.
     * @param array &$tokens Массив токенов.
     */
    public static function apply(int $index, array &$tokens): void
    {
        if ($tokens[$index]['type'] !== 'copy') {
            return;
        }

        $tokens[$index]['value'] = '©';
        TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
    }
}
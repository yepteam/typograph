<?php

namespace Yepteam\Typograph\Rules\Special;

/**
 * Заменяет (C) на символ ©.
 */
class ReplaceCopyright
{
    /**
     * @param int   $index  Индекс текущего токена.
     * @param array &$tokens Массив токенов.
     */
    public static function apply(int $index, array &$tokens): void
    {
        $token = $tokens[$index] ?? null;

        if (!$token || $token['type'] !== 'copy') {
            return;
        }

        $tokens[$index]['value'] = '©';
        $tokens[$index]['rule'] = __CLASS__ . ':' . __LINE__;
    }
}
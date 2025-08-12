<?php

namespace Yepteam\Typograph\Rules\Special;

class ReplaceRegMark
{
    /**
     * Заменяет токен типа 'reg', созданный токенизатором из (R) или (r),
     * на символ зарегистрированного товарного знака ®.
     *
     * @param int   $index   Индекс текущего токена.
     * @param array &$tokens Массив токенов.
     */
    public static function apply(int $index, array &$tokens): void
    {
        $currentToken = $tokens[$index] ?? null;

        if (!$currentToken || $currentToken['type'] !== 'reg') {
            return;
        }

        $tokens[$index]['type'] = 'char';
        $tokens[$index]['name'] = 'reg-mark';
        $tokens[$index]['value'] = '®';
        $tokens[$index]['rule'] = __CLASS__ . ':' . __LINE__;
    }
}
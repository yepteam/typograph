<?php

namespace Yepteam\Typograph\Rules\Special;

use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Rules\BaseRule;

class ReplaceRegMark extends BaseRule
{
    /**
     * Заменяет токен типа 'reg', созданный токенизатором из (R) или (r),
     * на символ зарегистрированного товарного знака ®.
     *
     * @param int $index Индекс текущего токена.
     * @param array &$tokens Массив токенов.
     */
    public static function apply(int $index, array &$tokens, array $options): void
    {
        if ($tokens[$index]['type'] !== 'reg') {
            return;
        }

        $tokens[$index]['type'] = 'char';
        $tokens[$index]['name'] = 'reg-mark';
        $tokens[$index]['value'] = '®';
        self::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
    }
}
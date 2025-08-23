<?php

namespace Yepteam\Typograph\Rules\Special;

use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Rules\BaseRule;

/**
 * Заменяет (C) на символ ©.
 */
class ReplaceCopyright extends BaseRule
{
    /**
     * @param int $index Индекс текущего токена.
     * @param array &$tokens Массив токенов.
     */
    public static function apply(int $index, array &$tokens, array $options): void
    {
        if ($tokens[$index]['type'] !== 'copy') {
            return;
        }

        $tokens[$index]['value'] = '©';
        !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
    }
}
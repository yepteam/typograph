<?php

namespace Yepteam\Typograph\Rules\Special;

use Yepteam\Typograph\Helpers\TokenHelper;

/**
 * Заменяет (C) на символ ©.
 */
class ReplaceTrademark
{
    /**
     * @param int   $index  Индекс текущего токена.
     * @param array &$tokens Массив токенов.
     */
    public static function apply(int $index, array &$tokens): void
    {
        if ($tokens[$index]['type'] !== 'trade') {
            return;
        }

        $prev_token_index = TokenHelper::findPrevToken($tokens, $index);

        if($prev_token_index === false){
            TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        if(in_array($tokens[$prev_token_index]['type'], ['space', 'nbsp']) ){
            TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        $tokens[$index]['value'] = '™';
        TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
    }
}
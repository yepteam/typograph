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
        $token = $tokens[$index] ?? null;

        if (!$token || $token['type'] !== 'trade') {
            return;
        }

        $prev_token_index = TokenHelper::findPrevToken($tokens, $index);

        if($prev_token_index === false){
            $tokens[$index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        if(in_array($tokens[$prev_token_index]['type'], ['space', 'nbsp']) ){
            $tokens[$index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        $tokens[$index]['value'] = '™';
        $tokens[$index]['rule'] = __CLASS__ . ':' . __LINE__;
    }
}
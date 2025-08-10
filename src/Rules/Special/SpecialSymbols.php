<?php

namespace Yepteam\Typograph\Rules\Special;

/**
 * Замена специальных символов на их HTML-представление
 *
 * - Замена токена copy на символ copyright ©
 * - Замена токена reg на зарегистрированную торговую марку в верхнем индексе
 * - Замена двойного дефиса на тире
 */
class SpecialSymbols
{
    public static function apply(int $index, array &$tokens): void
    {
        $token = $tokens[$index];

        switch ($token['type']) {
            case 'copy':
                $tokens[$index] = [
                    'type' => 'copy',
                    'value' => '©',
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
                break;
            case 'reg':
                $tokens[$index] = [
                    'type' => 'reg',
                    'value' => '<sup class="reg">&reg;</sup>',
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
                break;
            case 'plusmn':
                $tokens[$index] = [
                    'type' => 'plusmn',
                    'value' => '±',
                    'rule' => __CLASS__ . ':' . __LINE__,
                ];
                break;
        }
    }
}
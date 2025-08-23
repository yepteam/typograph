<?php

namespace Yepteam\Typograph\Rules\Special;

use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Rules\BaseRule;

/**
 * Заменяет последовательность "+-" на "±".
 */
class ReplacePlusMinus extends BaseRule
{
    /**
     * Правило применяется к токену "+".
     *
     * @param int   $index  Индекс текущего токена.
     * @param array &$tokens Массив токенов.
     */
    public static function apply(int $index, array &$tokens, array $options): void
    {
        // Правило должно срабатывать только на токене "+"
        if ($tokens[$index]['type'] !== 'plus') {
            return;
        }

        // Ищем следующий токен, который должен быть "-"
        $nextTokenIndex = $index + 1;
        if (!isset($tokens[$nextTokenIndex]) || $tokens[$nextTokenIndex]['type'] !== 'hyphen') {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        // Защита от ложных срабатываний в коде или URL (например, "C++-API" или "?a=1+-b")
        $prevTokenIndex = $index - 1;
        if (isset($tokens[$prevTokenIndex])) {
            $prevToken = $tokens[$prevTokenIndex];
            if (in_array($prevToken['value'], ['+', ':', '='])) {
                !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
                return;
            }
        }

        // 1. Модифицируем текущий токен "+" в "±"
        $tokens[$index]['type'] = 'punctuation';
        $tokens[$index]['name'] = 'plusmn';
        $tokens[$index]['value'] = '±';
        !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);

        // 2. "Удаляем" следующий токен "-", помечая его как пустой
        $tokens[$nextTokenIndex]['type'] = 'empty';
        $tokens[$nextTokenIndex]['value'] = '';
        !empty($options['debug']) && TokenHelper::logRule($tokens[$nextTokenIndex], __CLASS__ . ':' . __LINE__);
    }
}
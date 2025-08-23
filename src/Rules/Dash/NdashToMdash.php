<?php

namespace Yepteam\Typograph\Rules\Dash;

use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Rules\BaseRule;

/**
 * Заменяет дефис на длинное тире (игнорируя теги)
 */
class NdashToMdash extends BaseRule
{
    public static function apply(int $index, array &$tokens, array $options): void
    {
        // Применимо только к токену ndash
        if ($tokens[$index]['type'] !== 'ndash') {
            return;
        }

        // Токен ndash находится в начале строки, а после него есть пробел (игнорируя теги)
        if (TokenHelper::isAtStartOfLineWithSpace($tokens, $index)) {
            // Замена токена на mdash
            $tokens[$index] = [
                'type' => 'mdash',
                'value' => '—',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // ndash находится между числами (игнорируя теги)
        if (TokenHelper::isSurroundedByNumbers($tokens, $index)) {
            $tokens[$index] = [
                'type' => 'mdash',
                'value' => '—',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // ndash находится между пробелами (игнорируя теги)
        if (TokenHelper::isSurroundedBySpaces($tokens, $index)) {
            // Замена токена на mdash
            $tokens[$index] = [
                'type' => 'mdash',
                'value' => '—',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
            return;
        }

        !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
    }

}
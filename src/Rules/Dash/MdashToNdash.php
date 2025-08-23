<?php

namespace Yepteam\Typograph\Rules\Dash;

use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Rules\BaseRule;

/**
 * Заменяет mdash на ndash
 */
class MdashToNdash extends BaseRule
{
    public static function apply(int $index, array &$tokens, array $options): void
    {
        // Применимо только к токену mdash
        if ($tokens[$index]['type'] !== 'mdash') {
            return;
        }

        // mdash находится между числами (игнорируя теги)
        if (TokenHelper::isSurroundedByNumbers($tokens, $index)) {
            // Замена токена mdash на ndash
            $tokens[$index] = [
                'type' => 'ndash',
                'value' => '–',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
            return;
        }

        !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
    }
}
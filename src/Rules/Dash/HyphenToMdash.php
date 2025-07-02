<?php

namespace Yepteam\Typograph\Rules\Dash;

use Yepteam\Typograph\Helpers\TokenHelper;

/**
 * Заменяет дефис на mdash (игнорируя теги)
 */
class HyphenToMdash
{
    public static function apply(int $index, array &$tokens): void
    {
        $current = $tokens[$index];

        // Применимо только к дефису
        if (!in_array($current['type'], ['hyphen', 'double-hyphen', 'nbhy'])) {
            return;
        }

        // Токен представляет собой последовательность из двух дефисов
        if ($tokens[$index]['type'] === 'double-hyphen') {
            // Замена токена на mdash
            $tokens[$index] = [
                'type' => 'mdash',
                'value' => '—',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // Дефис находится в начале строки, а после него есть пробел (игнорируя теги)
        if (TokenHelper::isAtStartOfLineWithSpace($tokens, $index)) {
            // Замена токена на mdash
            $tokens[$index] = [
                'type' => 'mdash',
                'value' => '—',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // Дефис находится между пробелами (игнорируя теги)
        if (TokenHelper::isSurroundedBySpaces($tokens, $index)) {
            // Замена токена на mdash
            $tokens[$index] = [
                'type' => 'mdash',
                'value' => '—',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        $tokens[$index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
    }

}
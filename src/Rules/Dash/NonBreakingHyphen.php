<?php

namespace Yepteam\Typograph\Rules\Dash;

use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Rules\Formatting\HtmlEntities;

/**
 * Замена дефиса до или после числа на неразрывный пробел
 */
class NonBreakingHyphen
{
    public static function apply(int $index, array &$tokens, int $max_length): void
    {
        // Токен должен быть дефисом
        if ($tokens[$index]['type'] !== 'hyphen') {

            // Дефис может быть частью слова
            if ($tokens[$index]['type'] === 'word' && str_contains($tokens[$index]['value'], '-')) {
                self::applyToWord($index, $tokens, $max_length);
                return;
            }

            return;
        }

        $before_hyphen_index = TokenHelper::findPrevIgnoringTokens($tokens, $index);
        if ($before_hyphen_index === false) {
            $tokens[$index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        $after_hyphen_index = TokenHelper::findNextToken($tokens, $index);
        if ($after_hyphen_index === false) {
            $tokens[$index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        if (!in_array($tokens[$before_hyphen_index]['type'], ['word', 'number'])) {
            $tokens[$index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        if (!in_array($tokens[$after_hyphen_index]['type'], ['word', 'number'])) {
            $tokens[$index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        $shouldReplaceHyphen = false;

        $before_hyphen_length = mb_strlen($tokens[$before_hyphen_index]['value']);
        if ($before_hyphen_length <= $max_length) {
            $shouldReplaceHyphen = true;
        }

        $after_hyphen_length = mb_strlen($tokens[$after_hyphen_index]['value']);
        if ($after_hyphen_length <= $max_length) {
            $shouldReplaceHyphen = true;
        }

        if (!$shouldReplaceHyphen) {
            $tokens[$index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        // Заменяем токен hyphen на nbhy
        $tokens[$index] = [
            'type' => 'nbhy',
            'value' => HtmlEntities::decodeEntity('&#8209;'),
            'rule' => __CLASS__ . ':' . __LINE__,
        ];
    }

    public static function applyToWord(int $index, array &$tokens, int $max_length): void
    {
        $hyphen_count = substr_count($tokens[$index]['value'], '-');

        if ($hyphen_count !== 1) {
            $tokens[$index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        $word_parts = explode('-', $tokens[$index]['value']);

        $shouldReplaceHyphen = false;

        foreach ($word_parts as $part) {
            $part_len = mb_strlen($part);
            if ($part_len > 0 && $part_len <= $max_length) {
                $shouldReplaceHyphen = true;
            }
        }

        if (!$shouldReplaceHyphen) {
            $tokens[$index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        $tokens[$index]['value'] = str_replace(
            '-',
            HtmlEntities::decodeEntity('&#8209;'),
            $tokens[$index]['value']
        );
        $tokens[$index]['rule'] = __CLASS__ . ':' . __LINE__;
    }

}

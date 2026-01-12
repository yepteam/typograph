<?php

namespace Yepteam\Typograph\Rules\Dash;

use Yepteam\Typograph\Helpers\HtmlEntityHelper;
use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Rules\BaseRule;

final class NonBreakingHyphen extends BaseRule
{
    /**
     * Замена дефиса до или после числа на неразрывный пробел
     *
     * @param int $index Индекс токена
     * @param array $tokens Массив всех токенов
     * @param array $options Массив настроек типографа
     * @return void
     */
    public static function apply(int $index, array &$tokens, array $options): void
    {
        $max_length = $options['dash']['hyphen-to-nbhy'] ?? 0;

        if ($max_length === true) {
            $max_length = 2;
        }

        if (empty($max_length) || $max_length <= 0) {
            return;
        }

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
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        $after_hyphen_index = TokenHelper::findNextToken($tokens, $index);
        if ($after_hyphen_index === false) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        if (!in_array($tokens[$before_hyphen_index]['type'], ['word', 'number'])) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        if (!in_array($tokens[$after_hyphen_index]['type'], ['word', 'number'])) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
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
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        // Заменяем токен hyphen на nbhy
        $tokens[$index] = [
            'type' => 'nbhy',
            'value' => HtmlEntityHelper::decodeEntity('&#8209;'),
            'rule' => __CLASS__ . ':' . __LINE__,
        ];
        !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
    }

    public static function applyToWord(int $index, array &$tokens, int $max_length): void
    {
        $hyphen_count = substr_count($tokens[$index]['value'], '-');

        if ($hyphen_count !== 1) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
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
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        $tokens[$index]['value'] = str_replace(
            '-',
            HtmlEntityHelper::decodeEntity('&#8209;'),
            $tokens[$index]['value']
        );
        !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
    }

}

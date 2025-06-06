<?php

namespace Yepteam\Typograph\Rules\Nbsp;

use Yepteam\Typograph\Helpers\HtmlHelper;
use Yepteam\Typograph\Helpers\TokenHelper;

/**
 * Замена пробела до или после короткого слова на неразрывный пробел
 */
class ShortWord
{
    /**
     * Частицы
     */
    public static array $particles = [
        'б', // Было&nbsp;б чем гордиться
        'бы', // Я&nbsp;бы подумал
        'ли', // Море едва&nbsp;ли видно
        'же', // Съешь&nbsp;же ещё
        'ж', // Что&nbsp;ж мне делать?
    ];

    /**
     * Наречия
     */
    public static array $adverbs = [
        'уж', // вы&nbsp;уж не&nbsp;друг мой, вы&nbsp;уж не&nbsp;мой верный раб (Война и мир)
    ];

    /**
     * Расстановка неразрывных пробелов до и после короткого слова
     *
     * @param int $index Индекс токена
     * @param array $tokens Массив всех токенов
     * @param int|bool $max_length Максимальная длина короткого слова
     * @return void
     */
    public static function apply(int $index, array &$tokens, int|bool $max_length = 2): void
    {
        if ($max_length === true) {
            $max_length = 2;
        }

        // Токен должен быть словом или одиночным символом
        if (!in_array($tokens[$index]['type'], ['word', 'char'])) {
            return;
        }

        $current_word_length = mb_strlen($tokens[$index]['value']);

        // Токен должен быть коротким словом
        if ($current_word_length > $max_length) {
            return;
        }

        self::applyBefore($index, $tokens);

        self::applyAfter($index, $tokens);
    }

    public static function applyBefore(int $index, array &$tokens): void
    {
        $space_index = TokenHelper::findPrevToken($tokens, $index, 'space');

        // Предыдущий токен должен быть пробелом
        if ($space_index === false) {
            return;
        }

        $before_space_index = TokenHelper::findPrevToken($tokens, $space_index);
        if ($before_space_index === false) {
            // Ничего не делаем
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        // Если значение перед пробелом является наречием
        if (in_array($tokens[$before_space_index]['value'], self::$adverbs)) {
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        // Если значение является частицей
        if (in_array($tokens[$index]['value'], self::$particles)) {
            // Заменить пробел перед частицей словом на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // Если перед пробелом частица
        if (in_array($tokens[$before_space_index]['value'], self::$particles)) {
            // Ничего не делаем
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        // nbsp ставится, если перед пробелом:
        // - число или слово
        // - менее 3 символов
        if (preg_match('/.*[\p{L}\d]$/u', $tokens[$before_space_index]['value'])
            && mb_strlen($tokens[$before_space_index]['value']) < 3) {

            // Заменить пробел перед коротким словом на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // Предыдущий токен должен заканчивается одним из:
        // - буква
        // - цифра
        // - точка
        if (!preg_match('/.*[\p{L}\d.]$/u', $tokens[$before_space_index]['value'])) {
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        $next_index = TokenHelper::findNextToken($tokens, $index);

        // Если короткое слово стоит в конце или в конце предложения
        if ($next_index === false || in_array($tokens[$next_index]['value'], TokenHelper::$end_of_sentence_marks)) {

            // Заменить пробел перед коротким словом на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // Если после короткого слова примыкающий знак препинания
        if (in_array($tokens[$next_index]['value'], TokenHelper::$right_adjacent_marks)) {
            // Заменить пробел перед коротким словом на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // Если после короткого слова кавычка
        if ($tokens[$next_index]['type'] == 'quote') {
            // Заменить пробел перед коротким словом на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
                'rule' => __CLASS__ . ':' . __LINE__,
            ];
            return;
        }

        // Ничего не делаем
        $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
    }

    public static function applyAfter(int $index, array &$tokens): void
    {
        $space_index = TokenHelper::findNextToken($tokens, $index, 'space');

        // Следующий токен должен быть пробелом
        if ($space_index === false) {
            return;
        }

        $tag_index = TokenHelper::findNextToken($tokens, $index, 'tag');
        $after_space_tag_index = TokenHelper::findNextToken($tokens, $space_index, 'tag');

        if ($tag_index !== false && in_array($tokens[$tag_index]['name'], HtmlHelper::$new_line_tags)) {
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }
        if ($after_space_tag_index !== false && in_array($tokens[$after_space_tag_index]['name'], HtmlHelper::$new_line_tags)) {
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        // Если значение является частицей или наречием
        if (in_array($tokens[$index]['value'], array_merge(self::$particles, self::$adverbs))) {

            // Проверяем, есть ли nbsp перед частицей или наречием
            $prev_space_index = TokenHelper::findPrevToken($tokens, $index, 'nbsp');

            // Если есть nbsp перед частицей — выходим
            if ($prev_space_index !== false) {
                $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
                return;
            }
        }

        $prev_dash_token_index = TokenHelper::findPrevToken($tokens, $index, ['hyphen', 'ndash', 'mdash']);
        if ($prev_dash_token_index !== false) {
            $tokens[$space_index]['negative_rule'] = __CLASS__ . ':' . __LINE__;
            return;
        }

        // Заменить пробел после короткого слова на nbsp
        $tokens[$space_index] = [
            'type' => 'nbsp',
            'value' => ' ',
            'rule' => __CLASS__ . ':' . __LINE__,
        ];
    }
}

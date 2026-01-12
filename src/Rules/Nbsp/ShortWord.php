<?php

namespace Yepteam\Typograph\Rules\Nbsp;

use Yepteam\Typograph\Helpers\HtmlHelper;
use Yepteam\Typograph\Helpers\StringHelper;
use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Rules\BaseRule;

final class ShortWord extends BaseRule
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
     * @param array $options Массив настроек типографа
     * @return void
     */
    public static function apply(int $index, array &$tokens, array $options): void
    {
        $max_length = $options['nbsp']['short-word'] ?? 0;

        if ($max_length === true) {
            $max_length = 2;
        }

        if (empty($max_length) || $max_length <= 0) {
            return;
        }

        // Токен должен быть словом или одиночным символом
        // punctuation добавлен для &amp;
        if (!in_array($tokens[$index]['type'], ['word', 'char', 'punctuation'])) {
            return;
        }

        $current_word_length = mb_strlen($tokens[$index]['value']);

        // Токен должен быть коротким словом
        if ($current_word_length > $max_length) {
            return;
        }

        // Проверка допустимых символов
        // Разрешены:
        // - Буквы любого языка (\p{L})
        // - Символы валют (\p{Sc}, например $, €, £, ¥)
        // - Амперсанд (&)
        // - Дефис
        if (!preg_match('/^[\p{L}\p{Sc}&-]+$/u', $tokens[$index]['value'])) {
            return;
        }

        // Обрабатывает пробел перед коротким словом
        self::applyBefore($index, $tokens, $options, $max_length);

        // Обрабатывает пробел после короткого слова
        self::applyAfter($index, $tokens, $options);
    }

    /**
     * Установка неразрывного пробела перед коротким словом
     *
     * @param int $index
     * @param array $tokens
     * @param array $options
     * @param int $max_length
     * @return void
     */
    public static function applyBefore(int $index, array &$tokens, array $options, int $max_length): void
    {
        $space_index = TokenHelper::findPrevToken($tokens, $index, 'space');

        // Предыдущий токен должен быть пробелом
        if ($space_index === false) {
            return;
        }

        $before_space_index = TokenHelper::findPrevToken($tokens, $space_index);
        if ($before_space_index === false) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        $next_index = TokenHelper::findNextToken($tokens, $index);

        $is_number_before_space = $tokens[$before_space_index]['type'] === 'number';
        if ($is_number_before_space) {
            // За расстановку NBSP рядом с числами отвечает правило Nbsp\Number
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        // Если токен — амперсанд и перед пробелом точка
        if ($tokens[$index]['value'] === '&' && $tokens[$before_space_index]['value'] === '.') {
            $before_dot_token = TokenHelper::findPrevToken($tokens, $before_space_index);

            // Если перед точкой слово
            if ($tokens[$before_dot_token]['type'] === 'word') {
                // Заменить пробел перед коротким словом на nbsp
                $tokens[$space_index] = [
                    'type' => 'nbsp',
                    'value' => ' ',
                ];
                !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
                return;
            }
        }

        // Если значение перед пробелом является наречием
        if (in_array($tokens[$before_space_index]['value'], self::$adverbs)) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        // Если значение является частицей
        if (in_array($tokens[$index]['value'], self::$particles)) {
            // Заменить пробел перед частицей словом на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Если перед пробелом частица
        if (in_array($tokens[$before_space_index]['value'], self::$particles)) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        // Если перед пробелом слово в верхнем регистре
        if (preg_match('/.*[\p{L}\d]$/u', $tokens[$before_space_index]['value'])
            && StringHelper::isUpperCase($tokens[$before_space_index]['value'])) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        $is_word_before_space = $tokens[$before_space_index]['type'] === 'word';
        $is_short_word_before_space = $is_word_before_space && mb_strlen($tokens[$before_space_index]['value']) <= $max_length;

        // Значение перед пробелом — короткое слово?
        if ($is_short_word_before_space) {
            // Заменить пробел перед коротким словом на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Предыдущий токен должен заканчивается одним из:
        // - буква
        // - цифра
        // - точка
        // - амперсанд
        if (!preg_match('/.*[\p{L}\d.]$/u', $tokens[$before_space_index]['value'])) {
            // Исключение для амперсанда (FAMILY & CO)
            if ($tokens[$before_space_index]['value'] !== '&amp;') {
                !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
                return;
            }
        }

        // Если короткое слово стоит в конце или в конце предложения
        if ($next_index === false || in_array($tokens[$next_index]['value'], TokenHelper::$end_of_sentence_marks)) {

            // Заменить пробел перед коротким словом на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Если после короткого слова примыкающий знак препинания
        if (in_array($tokens[$next_index]['value'], TokenHelper::$right_adjacent_marks)) {
            // Заменить пробел перед коротким словом на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Если после короткого слова кавычка
        if ($tokens[$next_index]['type'] == 'quote') {
            // Заменить пробел перед коротким словом на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Если после короткого слова пробел
        if ($tokens[$next_index]['type'] !== 'space') {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        $after_space_index = TokenHelper::findNextToken($tokens, $next_index);

        if ($after_space_index === false) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        if ($tokens[$after_space_index]['type'] === 'number') {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        if (in_array($tokens[$after_space_index]['type'], ['hyphen', 'nbhy', 'ndash', 'mdash'])) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        if (mb_strlen($tokens[$before_space_index]['value']) <= $max_length) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        if (!StringHelper::isUpperCase($tokens[$index]['value'])) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        if (mb_strlen($tokens[$index]['value']) >= $max_length) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        // Заменить пробел перед коротким словом на nbsp
        $tokens[$space_index] = [
            'type' => 'nbsp',
            'value' => ' ',
        ];
        !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
    }

    /**
     * Установка неразрывного пробела после короткого слова
     *
     * @param int $index
     * @param array $tokens
     * @param array $options
     * @return void
     */
    public static function applyAfter(int $index, array &$tokens, array $options): void
    {
        $space_index = TokenHelper::findNextToken($tokens, $index, 'space');

        // Следующий токен должен быть пробелом
        if ($space_index === false) {
            return;
        }

        $tag_index = TokenHelper::findNextToken($tokens, $index, 'tag');
        $after_space_tag_index = TokenHelper::findNextToken($tokens, $space_index, 'tag');

        if ($tag_index !== false && in_array($tokens[$tag_index]['name'], HtmlHelper::$new_line_tags)) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        if ($after_space_tag_index !== false && in_array($tokens[$after_space_tag_index]['name'], HtmlHelper::$new_line_tags)) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        $after_space_non_tag_index = TokenHelper::findNextToken($tokens, $space_index);

        // После пробела нет ничего кроме тегов?
        if ($after_space_non_tag_index === false) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        $after_space_index = TokenHelper::findNextToken($tokens, $space_index);

        // Если после пробела entity
        if ($after_space_index !== false && $tokens[$after_space_index]['type'] === 'entity') {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        // Если после пробела один из символов
        if ($after_space_index !== false && preg_match('/^[.,!?;:+\-=<>|^*\/)\[\]{}—–]/u', $tokens[$after_space_index]['value'])) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        // Если значение является частицей или наречием
        if (in_array($tokens[$index]['value'], array_merge(self::$particles, self::$adverbs))) {
            // Проверяем, есть ли nbsp перед частицей или наречием
            $prev_space_index = TokenHelper::findPrevToken($tokens, $index, 'nbsp');

            // Если есть nbsp перед частицей — выходим
            if ($prev_space_index !== false) {
                !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
                return;
            }
        }

        $prev_dash_token_index = TokenHelper::findPrevToken($tokens, $index, ['hyphen', 'ndash', 'mdash']);
        if ($prev_dash_token_index !== false) {
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
            return;
        }

        $prev_token_index = TokenHelper::findPrevToken($tokens, $index);
        // Если перед коротким словом нет токена
        if (!$prev_token_index) {
            // Заменить пробел после короткого слова на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Предыдущий токен пробел?
        if ($tokens[$prev_token_index]['type'] === 'space') {
            // Заменить пробел после короткого слова на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
        }

        // Предыдущий токен nbsp?
        if ($tokens[$prev_token_index]['type'] === 'nbsp') {
            if (StringHelper::isUpperCase($tokens[$index]['value'])) {
                !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
                return;
            }

            // Заменить пробел после короткого слова на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Проверяем разрешенные символы слева
        if (in_array($tokens[$prev_token_index]['value'], ['(', '[', '{', '<', '&lt;', '/', '*'])) {
            // Заменить пробел после короткого слова на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Предыдущий токен кавычка?
        if (in_array($tokens[$prev_token_index]['type'], ['quote', 'rsquo'])) {
            // Заменить пробел после короткого слова на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Предыдущий токен перенос строки?
        if ($tokens[$prev_token_index]['type'] === 'new-line') {
            // Заменить пробел после короткого слова на nbsp
            $tokens[$space_index] = [
                'type' => 'nbsp',
                'value' => ' ',
            ];
            !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__);
            return;
        }

        !empty($options['debug']) && TokenHelper::logRule($tokens[$space_index], __CLASS__ . ':' . __LINE__, false);
    }
}

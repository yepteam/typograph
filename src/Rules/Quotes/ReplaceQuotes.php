<?php

namespace Yepteam\Typograph\Rules\Quotes;

use Yepteam\Typograph\Helpers\HtmlHelper;
use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Rules\BaseRule;

/**
 * Заменяет кавычки
 */
final class ReplaceQuotes extends BaseRule
{
    /**
     * Ключ опций, в котором передается состояние расстановки кавычек (QuotesState).
     * Typograph создает новое состояние для каждого вызова format().
     * Без него каждый вызов apply() начинает с нулевого уровня вложенности.
     */
    public const STATE_OPTION = 'quotes-state';

    /**
     * @deprecated Состояние больше не хранится в классе: Typograph передает его через опцию STATE_OPTION
     */
    public static function resetQuoteLevels(): void
    {
    }

    /**
     * @deprecated Кавычки берутся из опции quotes, переданной в apply()
     * @param array|false|null $quoteMarks
     * @return void
     */
    public static function setQuoteMarks(array|false|null $quoteMarks): void
    {
    }

    public static function apply(int $index, array &$tokens, array $options): void
    {
        /**
         * Кавычки по уровням вложенности.
         * Каждый элемент - массив из двух строк (открывающая и закрывающая кавычки)
         * @var array<array{string, string}> $quoteMarks
         */
        $quoteMarks = $options['quotes'] ?? [];

        // Не обрабатывать кавычки при пустом массиве
        if (empty($quoteMarks) || !is_array($quoteMarks)) {
            return;
        }

        // Применимо только к токену quote
        if ($tokens[$index]['type'] !== 'quote') {
            return;
        }

        $state = $options[self::STATE_OPTION] ?? null;
        if (!$state instanceof QuotesState) {
            $state = new QuotesState();
        }

        // Нужно ли заменить кавычку на Prime
        if (self::shouldBeReplacedWithPrime($tokens, $index, $quoteMarks)) {
            $tokens[$index]['type'] = 'Prime'; // &Prime;
            $tokens[$index]['value'] = '″'; // &Prime;
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Является ли текущая кавычка открывающей
        $isOpeningQuote = self::isOpeningQuote($tokens, $index, $quoteMarks);
        if (!empty($options['debug'])) {
            $tokens[$index]['isOpeningQuote'] = $isOpeningQuote;
        }

        // Для открывающей кавычки ищем соответствующую закрывающую
        if ($isOpeningQuote) {
            $closingIndex = self::findMatchingClosingQuote($tokens, $index, $quoteMarks);

            // Не нашли закрывающую кавычку?
            if ($closingIndex === null) {
                // Заменяем символ кавычки для этого уровня
                $tokens[$index]['value'] = self::getOpeningQuote($quoteMarks, $state->level);
                // Указываем уровень кавычки для отладки
                if (!empty($options['debug'])) {
                    $tokens[$index]['level'] = $state->level;
                }
                !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
                // Выходим, не меняя уровень вложенности
                return;
            }

            // Повышаем уровень кавычек
            $state->level++;
            // Отмечаем, что кавычка на этом уровне открыта
            $state->isOpen[$state->level] = true;
            // Заменяем символ кавычки для этого уровня
            $tokens[$index]['value'] = self::getOpeningQuote($quoteMarks, $state->level);
            // Указываем уровень кавычки для отладки
            if (!empty($options['debug'])) {
                $tokens[$index]['level'] = $state->level;
            }
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
        } else {
            // Проверяем, есть ли соответствующая открывающая кавычка
            $openingIndex = self::findMatchingOpeningQuote($tokens, $index, $quoteMarks);

            // Не нашли открывающую кавычку — оставляем как есть
            if ($openingIndex === null) {
                !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
                return;
            }

            // Заменяем символ кавычки для текущего уровня
            $tokens[$index]['value'] = self::getClosingQuote($quoteMarks, $state->level);
            // Указываем уровень кавычки для отладки
            if (!empty($options['debug'])) {
                $tokens[$index]['level'] = $state->level;
            }
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
            // Отмечаем, что кавычка на этом уровне закрыта
            $state->isOpen[$state->level] = false;
            // Понижаем уровень кавычек
            $state->level = max(-1, $state->level - 1);
        }

        if (!empty($options['debug'])) {
            $tokens[$index]['$isQuoteOpen'] = json_encode($state->isOpen);
        }
    }

    /**
     * Должен ли токен быть заменен на Prime
     *
     * @param array $tokens Массив всех токенов
     * @param int $index Идентификатор токена в массиве
     * @param array $quoteMarks Кавычки по уровням вложенности
     * @return bool
     */
    public static function shouldBeReplacedWithPrime(array $tokens, int $index, array $quoteMarks = []): bool
    {
        if ($tokens[$index]['type'] !== 'quote') {
            return false;
        }

        $isOpeningQuote = self::isOpeningQuote($tokens, $index, $quoteMarks);
        if ($isOpeningQuote) {
            return false;
        }

        // Проверяем, есть ли соответствующая открывающая кавычка
        $openingIndex = self::findMatchingOpeningQuote($tokens, $index, $quoteMarks);
        if ($openingIndex !== null) {
            return false;
        }

        // Перед Prime должно быть число
        $prev_number_index = TokenHelper::findPrevToken($tokens, $index, 'number');
        if ($prev_number_index === false) {
            return false;
        }

        // Проверяем токен перед числом, игнорируя пробел, nbsp и теги
        $before_number_index = TokenHelper::findPrevIgnoringTokens($tokens, $prev_number_index, ['space', 'nbsp', 'tag']);
        if ($before_number_index) {
            // Перед числом не должно быть знака номера
            if ($tokens[$before_number_index]['value'] === '№') {
                return false;
            }
            // Перед числом не должно быть знака решетки
            if ($tokens[$before_number_index]['value'] === '#') {
                return false;
            }
        }

        return true;
    }

    /**
     * Находит закрывающую кавычку, соответствующую открывающей на указанной позиции
     */
    private static function findMatchingClosingQuote(array $tokens, int $openingIndex, array $quoteMarks): ?int
    {
        $balance = 1;
        for ($i = $openingIndex + 1; $i < count($tokens); $i++) {
            if ($tokens[$i]['type'] === 'quote') {
                if (self::isOpeningQuote($tokens, $i, $quoteMarks)) {
                    $balance++;
                } else {
                    $balance--;
                    if ($balance === 0) {
                        return $i;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Находит открывающую кавычку, соответствующую закрывающей на указанной позиции
     */
    private static function findMatchingOpeningQuote(array $tokens, int $closingIndex, array $quoteMarks): ?int
    {
        $balance = 1;

        // Обход токенов справа налево
        for ($i = $closingIndex - 1; $i >= 0; $i--) {

            // Пропускаем токены, не являющиеся кавычкой
            if ($tokens[$i]['type'] !== 'quote') {
                continue;
            }

            // Найденная кавычка является открывающей?
            if (self::isOpeningQuote($tokens, $i, $quoteMarks)) {

                // Уменьшаем уровень вложенности кавычек
                $balance--;

                // Кавычка с таким же уровнем вложенности найдена?
                if ($balance === 0) {
                    // Возвращаем индекс найденного токена с кавычкой
                    return $i;
                }
            } else {
                // Увеличиваем уровень вложенности кавычек
                $balance++;
            }
        }

        return null;
    }

    /**
     * Определяет, является ли кавычка открывающей
     */
    private static function isOpeningQuote(array $tokens, int $index, array $quoteMarks): bool
    {
        $prev_index = TokenHelper::findPrevToken($tokens, $index);

        // Кавычка стоит в начале
        if ($prev_index === false) {
            return true;
        }

        // Кавычка стоит в начале строки
        if ($tokens[$prev_index]['type'] === 'new-line') {
            return true;
        }

        $next_index = TokenHelper::findNextToken($tokens, $index);

        // Кавычка стоит в конце
        if ($next_index === false) {
            return false;
        }

        $next_value = $tokens[$next_index]['value'];

        // Между кавычкой и следующим токеном есть тег переноса строки (<br>, </p> и т.п.)?
        // В этом случае кавычка фактически стоит в конце строки и не является открывающей,
        // даже если следующий значимый токен начинается на букву или цифру.
        $has_line_break_after = false;
        for ($i = $index + 1; $i < $next_index; $i++) {
            if ($tokens[$i]['type'] === 'tag' && in_array($tokens[$i]['name'], HtmlHelper::$new_line_tags)) {
                $has_line_break_after = true;
                break;
            }
        }

        // Если следующий токен начинается на:
        // - букву
        // - цифру
        if (!$has_line_break_after && preg_match('/^[\p{L}\d].*/u', $next_value)) {
            return true;
        }

        // Перед кавычкой стоит открывающая кавычка?
        foreach ($quoteMarks as $quoteLevel) {
            if (!isset($quoteLevel[0])) {
                continue;
            }
            if ($tokens[$prev_index]['value'] === $quoteLevel[0]) {
                return true;
            }
        }


        $prev_value = $tokens[$prev_index]['value'];

        // Если предыдущий токен заканчивается на:
        // - букву
        // - цифру
        // - знак препинания
        // - символ (+, =, ±, $, € и т.п.)
        if (preg_match('/.*[\p{L}\p{P}\p{S}\d]$/u', $prev_value)) {
            return false;
        }

        return true;
    }

    /**
     * Возвращает открывающую кавычку для текущего уровня вложенности
     */
    private static function getOpeningQuote(array $quoteMarks, int $level): string
    {
        if ($level < 0) {
            $level = 0;
        }

        return $quoteMarks[$level][0]
            ?? $quoteMarks[count($quoteMarks) - 1][0]
            ?? '«';
    }

    /**
     * Возвращает закрывающую кавычку для текущего уровня вложенности
     */
    private static function getClosingQuote(array $quoteMarks, int $level): string
    {
        if ($level < 0) {
            $level = 0;
        }

        return $quoteMarks[$level][1]
            ?? $quoteMarks[count($quoteMarks) - 1][1]
            ?? '»';
    }
}
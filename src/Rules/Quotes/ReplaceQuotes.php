<?php

namespace Yepteam\Typograph\Rules\Quotes;

use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Rules\BaseRule;

/**
 * Заменяет кавычки
 */
class ReplaceQuotes extends BaseRule
{
    /**
     * @var int Текущий уровень кавычек
     */
    private static int $quoteLevel = -1;

    /**
     * @var bool[] Массив с флагами открытости/закрытости кавычек на уровнях.
     * True - кавычка на уровне с этим индексом открыта
     * False - кавычка на уровне с этим индексом закрыта
     */
    private static array $isQuoteOpenArr = [];

    /**
     * Массив для хранения кавычек по уровням вложенности.
     * Каждый элемент - массив из двух строк (открывающая и закрывающая кавычки)
     * @var array<array{array{string, string}}>
     */
    private static array $quoteMarks = [];

    /**
     * Сброс счетчиков состояния кавычек
     */
    public static function resetQuoteLevels(): void
    {
        self::$quoteLevel = -1;
        self::$isQuoteOpenArr = [];
    }

    /**
     * Установка массива кавычек
     * @param array|false|null $quoteMarks
     * @return void
     */
    public static function setQuoteMarks(array|false|null $quoteMarks): void
    {
        if (empty($quoteMarks)) {
            self::$quoteMarks = [];
        } else {
            self::$quoteMarks = $quoteMarks;
        }
    }

    public static function apply(int $index, array &$tokens, array $options): void
    {
        // Не обрабатывать кавычки при пустом массиве
        if (empty(self::$quoteMarks)) {
            return;
        }

        // Применимо только к токену quote
        if ($tokens[$index]['type'] !== 'quote') {
            return;
        }

        // Нужно ли заменить кавычку на Prime
        if (self::shouldBeReplacedWithPrime($tokens, $index)) {
            $tokens[$index]['type'] = 'Prime'; // &Prime;
            $tokens[$index]['value'] = '″'; // &Prime;
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
            return;
        }

        // Является ли текущая кавычка открывающей
        $isOpeningQuote = self::isOpeningQuote($tokens, $index);
        $tokens[$index]['isOpeningQuote'] = $isOpeningQuote;

        // Для открывающей кавычки ищем соответствующую закрывающую
        if ($isOpeningQuote) {
            $closingIndex = self::findMatchingClosingQuote($tokens, $index);

            // Не нашли закрывающую кавычку?
            if ($closingIndex === null) {
                // Заменяем символ кавычки для этого уровня
                $tokens[$index]['value'] = self::getOpeningQuote(self::$quoteLevel);
                // Указываем уровень кавычки для отладки
                $tokens[$index]['level'] = self::$quoteLevel;
                !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
                // Выходим, не меняя уровень вложенности
                return;
            }

            // Повышаем уровень кавычек
            self::$quoteLevel++;
            // Отмечаем, что кавычка на этом уровне открыта
            self::$isQuoteOpenArr[self::$quoteLevel] = true;
            // Заменяем символ кавычки для этого уровня
            $tokens[$index]['value'] = self::getOpeningQuote(self::$quoteLevel);
            // Указываем уровень кавычки для отладки
            $tokens[$index]['level'] = self::$quoteLevel;
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
        } else {
            // Проверяем, есть ли соответствующая открывающая кавычка
            $openingIndex = self::findMatchingOpeningQuote($tokens, $index);

            // Не нашли открывающую кавычку — оставляем как есть
            if ($openingIndex === null) {
                !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__, false);
                return;
            }

            // Заменяем символ кавычки для текущего уровня
            $tokens[$index]['value'] = self::getClosingQuote(self::$quoteLevel);
            // Указываем уровень кавычки для отладки
            $tokens[$index]['level'] = self::$quoteLevel;
            !empty($options['debug']) && TokenHelper::logRule($tokens[$index], __CLASS__ . ':' . __LINE__);
            // Отмечаем, что кавычка на этом уровне закрыта
            self::$isQuoteOpenArr[self::$quoteLevel] = false;
            // Понижаем уровень кавычек
            self::$quoteLevel = max(-1, self::$quoteLevel - 1);
        }

        $tokens[$index]['$isQuoteOpen'] = json_encode(self::$isQuoteOpenArr);
    }

    /**
     * Должен ли токен быть заменен на Prime
     *
     * @param array $tokens Массив всех токенов
     * @param int $index Идентификатор токена в массиве
     * @return bool
     */
    public static function shouldBeReplacedWithPrime(array $tokens, int $index): bool
    {
        if ($tokens[$index]['type'] !== 'quote') {
            return false;
        }

        $isOpeningQuote = self::isOpeningQuote($tokens, $index);
        if ($isOpeningQuote) {
            return false;
        }

        // Проверяем, есть ли соответствующая открывающая кавычка
        $openingIndex = self::findMatchingOpeningQuote($tokens, $index);
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
    private static function findMatchingClosingQuote(array $tokens, int $openingIndex): ?int
    {
        $balance = 1;
        for ($i = $openingIndex + 1; $i < count($tokens); $i++) {
            if ($tokens[$i]['type'] === 'quote') {
                if (self::isOpeningQuote($tokens, $i)) {
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
    private static function findMatchingOpeningQuote(array $tokens, int $closingIndex): ?int
    {
        $balance = 1;

        // Обход токенов справа налево
        for ($i = $closingIndex - 1; $i >= 0; $i--) {

            // Пропускаем токены, не являющиеся кавычкой
            if ($tokens[$i]['type'] !== 'quote') {
                continue;
            }

            // Найденная кавычка является открывающей?
            if (self::isOpeningQuote($tokens, $i)) {

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
    private static function isOpeningQuote(array $tokens, int $index): bool
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

        // Если следующий токен начинается на:
        // - букву
        // - цифру
        if (preg_match('/^[\p{L}\d].*/u', $next_value)) {
            return true;
        }

        // Перед кавычкой стоит открывающая кавычка?
        foreach (self::$quoteMarks as $quoteLevel) {
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
        if (preg_match('/.*[\p{L}\p{P}\d]$/u', $prev_value)) {
            return false;
        }

        return true;
    }

    /**
     * Возвращает открывающую кавычку для текущего уровня вложенности
     */
    private static function getOpeningQuote(int $level): string
    {
        if ($level < 0) {
            $level = 0;
        }

        return self::$quoteMarks[$level][0]
            ?? self::$quoteMarks[count(self::$quoteMarks) - 1][0]
            ?? '«';
    }

    /**
     * Возвращает закрывающую кавычку для текущего уровня вложенности
     */
    private static function getClosingQuote(int $level): string
    {
        if ($level < 0) {
            $level = 0;
        }

        return self::$quoteMarks[$level][1]
            ?? self::$quoteMarks[count(self::$quoteMarks) - 1][1]
            ?? '»';
    }
}
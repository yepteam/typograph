<?php

namespace Yepteam\Typograph\Rules\Quotes;

use Yepteam\Typograph\Helpers\TokenHelper;

/**
 * Заменяет кавычки
 */
class ReplaceQuotes
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

    public static function apply(int $index, array &$tokens): void
    {
        // Не обрабатывать кавычки при пустом массиве
        if (empty(self::$quoteMarks)) {
            return;
        }

        $current = $tokens[$index];

        // Применимо только к токену quote
        if ($current['type'] !== 'quote') {
            return;
        }

        // Нужно ли заменить кавычку на Prime
        if (self::shouldBeReplacedWithPrime($tokens, $index)) {
            $current['type'] = 'Prime'; // &Prime;
            $current['value'] = '″'; // &Prime;
            $current['rule'] = __CLASS__ . ':' . __LINE__;
            $tokens[$index] = $current;
            return;
        }

        // Является ли текущая кавычка открывающей
        $isOpeningQuote = self::isOpeningQuote($tokens, $index);
        $current['isOpeningQuote'] = $isOpeningQuote;

        // Для открывающей кавычки ищем соответствующую закрывающую
        if ($isOpeningQuote) {
            $closingIndex = self::findMatchingClosingQuote($tokens, $index);
            if ($closingIndex === null) {
                $current['negative_rule'] = __CLASS__ . ':' . __LINE__;
                $tokens[$index] = $current;
                return; // Не нашли закрывающую кавычку - оставляем как есть
            }

            // Повышаем уровень кавычек
            self::$quoteLevel++;
            // Отмечаем, что кавычка на этом уровне открыта
            self::$isQuoteOpenArr[self::$quoteLevel] = true;
            // Заменяем символ кавычки для этого уровня
            $current['value'] = self::getOpeningQuote(self::$quoteLevel);
            // Указываем уровень кавычки для отладки
            $current['level'] = self::$quoteLevel;
        } else {
            // Проверяем, есть ли соответствующая открывающая кавычка
            $openingIndex = self::findMatchingOpeningQuote($tokens, $index);
            if ($openingIndex === null) {
                $current['negative_rule'] = __CLASS__ . ':' . __LINE__;
                $tokens[$index] = $current;
                return; // Не нашли открывающую кавычку - оставляем как есть
            }

            // Заменяем символ кавычки для текущего уровня
            $current['value'] = self::getClosingQuote(self::$quoteLevel, self::$isQuoteOpenArr, $tokens, $index);
            // Указываем уровень кавычки для отладки
            $current['level'] = self::$quoteLevel;
            // Отмечаем, что кавычка на этом уровне закрыта
            self::$isQuoteOpenArr[self::$quoteLevel] = false;
            // Понижаем уровень кавычек
            self::$quoteLevel = max(-1, self::$quoteLevel - 1);
        }

        $current['rule'] = basename(__CLASS__);
        $current['$isQuoteOpen'] = json_encode(self::$isQuoteOpenArr);
        $tokens[$index] = $current;
    }

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

        $prev_number_index = TokenHelper::findPrevToken($tokens, $index, 'number');
        if ($prev_number_index === false) {
            return false;
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
        for ($i = $closingIndex - 1; $i >= 0; $i--) {
            if ($tokens[$i]['type'] === 'quote') {
                if (self::isOpeningQuote($tokens, $i)) {
                    $balance--;
                    if ($balance === 0) {
                        return $i;
                    }
                } else {
                    $balance++;
                }
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

        // Кавычка стоит в начале текста — открывающая
        if ($prev_index === false) {
            return true;
        }

        // Перед кавычкой стоит открывающая кавычка-елочка?
        if ($tokens[$prev_index]['value'] === '«') {
            return true;
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
        return self::$quoteMarks[$level][0]
            ?? self::$quoteMarks[count(self::$quoteMarks) - 1][0]
            ?? '«';
    }

    /**
     * Возвращает закрывающую кавычку для текущего уровня вложенности
     */
    private static function getClosingQuote(int $level): string
    {
        return self::$quoteMarks[$level][1]
            ?? self::$quoteMarks[count(self::$quoteMarks) - 1][1]
            ?? '»';
    }
}
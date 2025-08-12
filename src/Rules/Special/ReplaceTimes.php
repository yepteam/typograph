<?php

namespace Yepteam\Typograph\Rules\Special;

/**
 * Заменяет x между числами на знак умножения '×'
 */
class ReplaceTimes
{
    const NUMBER_PATTERN = '\d+([.,]\d+)?';

    public static function apply(int $index, array &$tokens): void
    {
        self::handleConcatenatedCase($index, $tokens);
        self::handleSpacedCase($index, $tokens);
    }

    /**
     * Обрабатывает слитные случаи: "150x200", "10.5x5,5x100".
     */
    private static function handleConcatenatedCase(int $index, array &$tokens): void
    {
        $currentToken = $tokens[$index] ?? null;
        $prevToken = $tokens[$index - 1] ?? null;

        if (
            !$currentToken || $currentToken['type'] !== 'word' ||
            !$prevToken || $prevToken['type'] !== 'number' ||
            !preg_match('/^[xх]/u', $currentToken['value'])
        ) {
            return;
        }

        // Используем preg_match_all для разбора строки на части "x" + "число"
        $pattern = '/([xх])(' . self::NUMBER_PATTERN . ')/u';
        preg_match_all($pattern, $currentToken['value'], $matches, PREG_SET_ORDER);

        // Проверяем, что вся строка состоит только из валидных сегментов
        $matchedLength = 0;
        foreach ($matches as $match) {
            $matchedLength += mb_strlen($match[0]);
        }
        if ($matchedLength !== mb_strlen($currentToken['value'])) {
            return; // Строка содержит что-то кроме "xЧИСЛО", например "x200foo"
        }

        $resultSuffix = '';
        foreach ($matches as $match) {
            // $match[1] это 'x' или 'х', $match[2] это число
            $resultSuffix .= '×' . $match[2];
        }

        if (!empty($resultSuffix)) {
            $tokens[$index - 1]['value'] .= $resultSuffix;
            $tokens[$index - 1]['type'] = 'word'; // Это уже не чистое число
            $tokens[$index - 1]['rule'] = __CLASS__ . ':' . __LINE__;

            $tokens[$index]['type'] = 'empty';
            $tokens[$index]['value'] = '';
        }
    }

    /**
     * Обрабатывает раздельные случаи: "2 x 2", "10.5 x 5.5", "10 x 20 x 30".
     */
    private static function handleSpacedCase(int $index, array &$tokens): void
    {
        $currentToken = $tokens[$index] ?? null;

        if (!$currentToken || $currentToken['type'] !== 'word' || !in_array($currentToken['value'], ['x', 'х'])) {
            return;
        }

        $prevToken = $tokens[$index - 1] ?? null;
        $prevPrevToken = $tokens[$index - 2] ?? null;
        $nextToken = $tokens[$index + 1] ?? null;
        $nextNextToken = $tokens[$index + 2] ?? null;

        // проверяем space или nbsp, чтобы избежать конфликта правил.
        $isPrevNumber = (
            $prevPrevToken && $prevPrevToken['type'] === 'number' &&
            $prevToken && in_array($prevToken['type'], ['space', 'nbsp'])
        );

        $isNextNumber = (
            $nextNextToken && $nextNextToken['type'] === 'number' &&
            $nextToken && in_array($nextToken['type'], ['space', 'nbsp'])
        );

        if (!$isPrevNumber || !$isNextNumber) {
            return;
        }

        $tokens[$index]['type'] = 'punctuation';
        $tokens[$index]['name'] = 'times';
        $tokens[$index]['value'] = '×';
        $tokens[$index]['rule'] = __CLASS__ . ':' . __LINE__;

        // Удаляем окружающие пробелы/nbsp
        $tokens[$index - 1]['type'] = 'empty';
        $tokens[$index - 1]['value'] = '';

        $tokens[$index + 1]['type'] = 'empty';
        $tokens[$index + 1]['value'] = '';
    }
}
<?php

namespace Yepteam\Typograph\Helpers;

final class StringHelper
{
    /**
     * Знаки валют
     */
    public static array $currency_symbols = [
        '$', // Символ доллара
        '£', // Символ фунта (лиры)
        '¤', // Символ валюты
        '¥', // Символ иены (юаня)
        'Ұ', // Символ иены (юаня)
        '₤', // Символ фунта (лиры)
        '₪', // Символ нового шекеля
        '€', // Символ евро
        '₴', // Символ гривны
        '₸', // Символ тенге
        '₹', // Символ индийской рупии
        '₽', // Символ российского рубля
    ];

    /**
     * Начинается ли строка с большой буквы
     * @param string $value
     * @return bool
     */
    public static function isUcFirstValue(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        $firstChar = mb_substr($value, 0, 1);

        return mb_strtoupper($firstChar) === $firstChar;
    }

    /**
     * Проверяет, что строка является словом (может содержать дефисы и апострофы),
     * первая буква — заглавная, а все остальные символы — строчные.
     * @param string $value
     * @return bool
     */
    public static function isLowerCaseExceptFirst(string $value): bool
    {
        if (mb_strlen($value) === 0) {
            return false;
        }

        // Проверяем, что строка состоит только из букв, дефисов или апострофов
        $isWord = preg_match('/^[\p{L}\-\']+$/u', $value) === 1;
        if (!$isWord) {
            return false;
        }

        // Первый символ должен быть заглавной буквой (если это буква)
        $firstChar = mb_substr($value, 0, 1);
        $isFirstUpper = mb_strtoupper($firstChar) === $firstChar && mb_strtolower($firstChar) !== $firstChar;

        // Остальная часть строки должна быть в нижнем регистре
        $restOfString = mb_substr($value, 1);
        $isRestLower = mb_strtolower($restOfString) === $restOfString;

        return $isFirstUpper && $isRestLower;
    }

    /**
     * Проверяет, что строка является словом, и все символы в нем, — прописные
     * @param string $value
     * @return bool
     */
    public static function isLowerCase(string $value): bool
    {
        // Проверяем, что строка состоит только из букв, дефисов или апострофов
        $isWord = preg_match('/^[\p{L}\-\']+$/u', $value) === 1;

        // Проверяем, что все символы в нижнем регистре
        $isLowercase = mb_strtolower($value) === $value;

        return $isWord && $isLowercase;
    }

    /**
     * Начинается ли строка с цифры или буквы
     * @param string $value
     * @return bool
     */
    public static function isStartsWithAlphaNumeric(string $value): bool
    {
        return preg_match('/^[\d\p{L}]/u', $value) === 1;
    }

    /**
     * Заканчивается ли строка цифрой или буквой
     * @param string $value
     * @return bool
     */
    public static function isEndsWithAlphaNumeric(string $value): bool
    {
        return preg_match('/[\d\p{L}]$/u', $value) === 1;
    }

    /**
     * Заканчивается ли строка цифрой
     * @param string $value
     * @return bool
     */
    public static function isEndsWithNumber(string $value): bool
    {
        return preg_match('/[1-9]$/', $value) === 1;
    }

    /**
     * Находится ли всё строка в верхнем регистре
     * @param mixed $value
     * @return bool
     */
    public static function isUpperCase(mixed $value): bool
    {
        // Проверяем, что строка состоит только из букв, дефисов или апострофов
        $isWord = preg_match('/^[\p{L}\-\']+$/u', $value) === 1;

        // Проверяем, что все символы в верхнем регистре
        $isUppercase = mb_strtoupper($value) === $value;

        return $isWord && $isUppercase;
    }

}
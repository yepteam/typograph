<?php

namespace Yepteam\Typograph\Rules\Formatting;

/**
 * Преобразование символов в HTML-сущности (кроме тегов)
 */
class HtmlEntities
{
    public static function applyToAll(array &$tokens): void
    {
        for ($index = 0; $index < count($tokens); $index++) {

            // Пропускаем теги
            if ($tokens[$index]['type'] === 'tag') {
                continue;
            }

            $value = $tokens[$index]['value'];

            // Пропускаем уже преобразованные сущности
            if (preg_match('/&(?:[a-z]+|#\d+);/i', $value)) {
                continue;
            }

            $value = htmlentities($value);

            // Ко́шка → Ко&#769;шка
            $value = str_replace("\xCC\x81", '&#769;', $value);

            // № → &#8470;
            $value = str_replace("№", '&#8470;', $value);

            $tokens[$index]['value'] = $value;
        }
    }
}

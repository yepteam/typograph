<?php

namespace Yepteam\Typograph\Rules\Formatting;

use Yepteam\Typograph\Helpers\TokenHelper;
use Yepteam\Typograph\Tokenizer;

/**
 * Преобразование HTML-сущностей обратно в символы
 */
class HtmlEntitiesDecode
{
    public static function applyToAll(array &$tokens): void
    {
        for ($index = 0; $index < count($tokens); $index++) {

            // Пропускаем теги
            if ($tokens[$index]['type'] === 'tag') {
                continue;
            }

            $value = $tokens[$index]['value'];

            // Пропускаем токены без HTML-сущностей
            if (!preg_match('/&(?:[a-z]+|#\d+);/i', $value)) {
                continue;
            }

            $tokens[$index]['value'] = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
}

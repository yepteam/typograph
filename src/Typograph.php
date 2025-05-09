<?php

namespace Yepteam\Typograph;

use Yepteam\Typograph\Rules\{Dash, Nbsp, Formatting, Punctuation, Quotes, Special};

class Typograph
{

    public static function format(string $text, &$tokens = [], $decode = false): string
    {
        $tokenizer = new Tokenizer();

        $tokens = $tokenizer->tokenize($text);

        // Сброс счетчиков состояния кавычек
        Quotes\ReplaceQuotes::resetQuoteLevels();

        for ($index = 0; $index < count($tokens); $index++) {

            // Замена некоторых символов
            Special\SpecialSymbols::apply($index, $tokens);

            // Замена hyphen на minus
            Dash\HyphenToMinus::apply($index, $tokens);

            // Замена hyphen на mdash
            Dash\HyphenToMdash::apply($index, $tokens);

            // Замена ndash на mdash
            Dash\NdashToMdash::apply($index, $tokens);

            // Замена mdash на ndash
            Dash\MdashToNdash::apply($index, $tokens);

            // Замена пробела на nbsp до или после короткого слова
            Nbsp\ShortWord::apply($index, $tokens);

            // Замена пробела на nbsp до или после числа
            Nbsp\Number::apply($index, $tokens);

            // Замена пробела на nbsp до или после mdash
            Nbsp\Mdash::apply($index, $tokens);

            // Замена пробела на nbsp до или после инициала
            Nbsp\Initial::apply($index, $tokens);

            // Замена многоточия на три точки
            //Punctuation\HellipToThreeDots::apply($index, $tokens);

            // Замена трёх точек на многоточие
            Punctuation\ThreeDotsToHellip::apply($index, $tokens);

            // Замена апострофа
            Quotes\ReplaceApos::apply($index, $tokens);

            // Замена кавычек
            Quotes\ReplaceQuotes::apply($index, $tokens);
        }

        if (!$decode) {
            // Преобразование символов в HTML-сущности (кроме тегов)
            Formatting\HtmlEntities::applyToAll($tokens);
        }

        return $tokenizer->toString($tokens);
    }

}

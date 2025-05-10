<?php

namespace Yepteam\Typograph;

use Yepteam\Typograph\Rules\{Dash, Formatting, Nbsp, Punctuation, Quotes, Special};

class Typograph
{
    /**
     * @var array Массив для хранения токенов текста
     */
    private array $tokens = [];

    /**
     * Форматирует текст с применением типографских правил
     *
     * @param string $text Исходный текст для обработки
     * @param bool $use_symbols Флаг замены буквенных кодов на готовые символы
     * @return string Обработанный текст с применением типографских правил
     */
    public function format(string $text, bool $use_symbols = false): string
    {
        $tokenizer = new Tokenizer();

        // Преобразование текста в массив токенов
        $this->tokens = $tokenizer->tokenize($text);

        // Сброс счетчиков состояния кавычек
        Quotes\ReplaceQuotes::resetQuoteLevels();

        for ($index = 0; $index < count($this->tokens); $index++) {

            // Замена некоторых символов
            Special\SpecialSymbols::apply($index, $this->tokens);

            // Замена hyphen на minus
            Dash\HyphenToMinus::apply($index, $this->tokens);

            // Замена hyphen на mdash
            Dash\HyphenToMdash::apply($index, $this->tokens);

            // Замена ndash на mdash
            Dash\NdashToMdash::apply($index, $this->tokens);

            // Замена mdash на ndash
            Dash\MdashToNdash::apply($index, $this->tokens);

            // Замена пробела на nbsp до или после короткого слова
            Nbsp\ShortWord::apply($index, $this->tokens);

            // Замена пробела на nbsp до или после числа
            Nbsp\Number::apply($index, $this->tokens);

            // Замена пробела на nbsp до или после mdash
            Nbsp\Mdash::apply($index, $this->tokens);

            // Замена пробела на nbsp до или после инициала
            Nbsp\Initial::apply($index, $this->tokens);

            // Замена многоточия на три точки
            //Punctuation\HellipToThreeDots::apply($index, $this->tokens);

            // Замена трёх точек на многоточие
            Punctuation\ThreeDotsToHellip::apply($index, $this->tokens);

            // Замена апострофа
            Quotes\ReplaceApos::apply($index, $this->tokens);

            // Замена кавычек
            Quotes\ReplaceQuotes::apply($index, $this->tokens);
        }

        if (!$use_symbols) {
            // Преобразование символов в HTML-сущности (кроме тегов)
            Formatting\HtmlEntities::applyToAll($this->tokens);
        }

        return $tokenizer->toString($this->tokens);
    }

    /**
     * Возвращает массив токенов после обработки
     *
     * @return array Массив токенов
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

}

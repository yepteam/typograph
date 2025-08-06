<?php

namespace Yepteam\Typograph;

use Yepteam\Typograph\Rules\{Dash, Formatting, Nbsp, Punctuation, Quotes, Special};

class Typograph
{
    /**
     * @param array{
     *      // Режим кодирования
     *      // named - буквенными кодами
     *      // numeric - числовыми кодами
     *      // hex - шестнадцатеричными кодами
     *      // raw - готовыми символами
     *      entities?: 'named'|'numeric'|'hex'|'raw',
     *
     *      // Многоточия
     *      // hellip - замена трех точек на символ многоточия
     *      // dots - замена символа многоточия на три точки
     *      // none - не обрабатывать многоточия
     *      ellipsis?: 'hellip'|'dots'|'none',
     *
     *      // Массив кавычек по каждому уровню
     *      // [] - не обрабатывать кавычки
     *      // [
     *      //   ['«', '»'], // Кавычки 1 уровня
     *      //   ['„', '“'], // Кавычки 2 уровня
     *      // ]
     *      quotes?: array<int, array{string, string}>,
     *
     *      // Правила замены знаков минус/дефис/тире
     *      dash?: array<string, array{
     *          'hyphen-to-mdash', // дефис на Mdash
     *          'hyphen-to-minus', // дефис на минус
     *          'mdash-to-ndash', // Mdash на Ndash
     *          'ndash-to-mdash', // Ndash на Mdash
     *          'hyphen-to-nbhy', // обычный дефис на неразрывный
     *      }>,
     *
     *      // Правила расстановки неразрывных пробелов
     *      nbsp?: array<string, array{
     *          'initial', // До и после инициалов
     *          'mdash', // До и после тире
     *          'number', // До и после числа
     *          'short-word', // До и после короткого слова
     *      }>
     *  }|bool $options Массив опций или флаг замены буквенных кодов на готовые символы
     *  true - Форматирование готовыми символами
     *  false - Форматирование буквенными кодами
     */
    public function __construct(array|bool $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * Задает параметры типографа.
     * @param array|bool $options
     * @return void
     * @see __construct
     */
    public function setOptions(array|bool $options): void
    {
        if ($options === false) {
            $options = [
                'entities' => 'named'
            ];
        }
        if ($options === true) {
            $options = [
                'entities' => 'raw'
            ];
        }

        $this->options = array_merge($this->defaultOptions, $options);
    }

    /**
     * @var array Массив для хранения параметров типографа
     */
    private array $options = [];

    /**
     * @var array Массив для хранения токенов текста
     */
    private array $tokens = [];

    /**
     * @var array Параметры по умолчанию
     */
    private array $defaultOptions = [
        'entities' => 'raw',
        'ellipsis' => 'hellip',
        'quotes' => [
            ['«', '»'],
            ['„', '“'],
        ],
        'dash' => [
            'hyphen-to-mdash' => true,
            'hyphen-to-minus' => true,
            'mdash-to-ndash' => true,
            'ndash-to-mdash' => true,
            'hyphen-to-nbhy' => 2,
        ],
        'nbsp' => [
            'initial' => true,
            'mdash' => true,
            'number' => true,
            'short-word' => true,
        ],
    ];

    /**
     * Форматирует текст с применением типографских правил
     *
     * @param string $text Текст для обработки
     * @return string Обработанный текст с применением типографских правил
     */
    public function format(string $text): string
    {
        $tokenizer = new Tokenizer();

        // Преобразование текста в массив токенов
        $this->tokens = $tokenizer->tokenize($text);

        // Установка массива кавычек
        Quotes\ReplaceQuotes::setQuoteMarks($this->options['quotes'] ?? []);

        // Сброс счетчиков состояния кавычек
        Quotes\ReplaceQuotes::resetQuoteLevels();

        for ($index = 0; $index < count($this->tokens); $index++) {

            // Замена некоторых символов
            Special\SpecialSymbols::apply($index, $this->tokens);

            // Правила замены знаков минус/дефис/тире
            if (isset($this->options['dash'])) {

                if (!empty($this->options['dash']['hyphen-to-minus'])) {
                    // Замена дефиса на минус
                    Dash\HyphenToMinus::apply($index, $this->tokens);
                }

                if (!empty($this->options['dash']['hyphen-to-mdash'])) {
                    // Замена дефиса на mdash
                    Dash\HyphenToMdash::apply($index, $this->tokens);
                }

                if (!empty($this->options['dash']['ndash-to-mdash'])) {
                    // Замена ndash на mdash
                    Dash\NdashToMdash::apply($index, $this->tokens);
                }

                if (!empty($this->options['dash']['mdash-to-ndash'])) {
                    // Замена mdash на ndash
                    Dash\MdashToNdash::apply($index, $this->tokens);
                }

                if (!empty($this->options['dash']['hyphen-to-nbhy'])) {
                    // Замена обычного дефиса на неразрывный
                    Dash\NonBreakingHyphen::apply($index, $this->tokens, $this->options['dash']['hyphen-to-nbhy']);
                }
            }

            // Правила расстановки неразрывных пробелов
            if (isset($this->options['nbsp'])) {

                if (!empty($max_length = $this->options['nbsp']['short-word'])) {
                    // Замена пробела на nbsp до или после короткого слова
                    Nbsp\ShortWord::apply($index, $this->tokens, $max_length);
                }

                if (!empty($this->options['nbsp']['number'])) {
                    // Замена пробела на nbsp до или после числа
                    Nbsp\Number::apply($index, $this->tokens);
                }

                if (!empty($this->options['nbsp']['mdash'])) {
                    // Замена пробела на nbsp до или после mdash
                    Nbsp\Mdash::apply($index, $this->tokens);
                }

                if (!empty($this->options['nbsp']['initial'])) {
                    // Замена пробела на nbsp до или после инициала
                    Nbsp\Initial::apply($index, $this->tokens);
                }
            }

            if ($this->options['ellipsis'] === 'hellip') {
                // Замена трёх точек на многоточие
                Punctuation\ThreeDotsToHellip::apply($index, $this->tokens);
            }

            if ($this->options['ellipsis'] === 'dots') {
                // Замена многоточия на три точки
                Punctuation\HellipToThreeDots::apply($index, $this->tokens);
            }

            // Замена апострофа
            Quotes\ReplaceApos::apply($index, $this->tokens);

            // Замена кавычек
            Quotes\ReplaceQuotes::apply($index, $this->tokens);
        }

        // Преобразование символов
        Formatting\HtmlEntities::applyToAll($this->tokens, $this->options['entities']);

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

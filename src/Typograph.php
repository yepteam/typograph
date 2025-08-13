<?php

namespace Yepteam\Typograph;

use Yepteam\Typograph\Rules\{Dash, Formatting, Nbsp, Punctuation, Quotes, Special};

class Typograph
{
    private array $metrics = [
        'originalContentLength' => 0,
        'resultContentLength' => 0,
        'tokenizationTime' => 0.0,
        'processingTime' => 0.0,
        'memoryUsage' => 0.0,
        'totalTime' => 0.0,
    ];

    private Tokenizer $tokenizer;

    /**
     * @param array{
     *     // Режим кодирования
     *     // named - буквенными кодами
     *     // numeric - числовыми кодами
     *     // hex - шестнадцатеричными кодами
     *     // raw - готовыми символами
     *     entities?: 'named'|'numeric'|'hex'|'raw',
     *
     *     // Многоточия
     *     // hellip - замена трех точек на символ многоточия
     *     // dots - замена символа многоточия на три точки
     *     // none - не обрабатывать многоточия
     *     ellipsis?: 'hellip'|'dots'|'none',
     *
     *     // Массив кавычек по каждому уровню
     *     // [] - не обрабатывать кавычки
     *     // [
     *     //   ['«', '»'], // Кавычки 1 уровня
     *     //   ['„', '“'], // Кавычки 2 уровня
     *     // ]
     *     quotes?: array<int, array{string, string}>|false,
     *
     *     // Правила замены знаков минус/дефис/тире
     *     dash?: array{
     *         'hyphen-to-mdash'?: bool, // дефис на mdash
     *         'hyphen-to-minus'?: bool, // дефис на минус
     *         'mdash-to-ndash'?: bool, // mdash на ndash
     *         'ndash-to-mdash'?: bool, // ndash на mdash
     *         'hyphen-to-nbhy'?: bool|int, // неразрывный дефис
     *     },
     *
     *     // Правила расстановки неразрывных пробелов
     *     nbsp?: array{
     *         'initial'?: bool, // до и после инициалов
     *         'mdash'?: bool, // до и после тире
     *         'number'?: bool, // до и после числа
     *         'short-word'?: bool|int, // до и после короткого слова
     *     },
     *
     *     // Правила для специальных символов
     *     special?: array{
     *         'copyright'?: bool, // (C) -> © (&copy;)
     *         'plus-minus'?: bool, // замена +- на ± (&plusmn;)
     *         'reg-mark'?: bool,  // (R) -> ® (&reg;)
     *         'times'?: bool, // замена x на × (&times;) — между числами
     *         'trade'?: bool, // замена (tm) на ™
     *     }
     *  }|bool $options Массив опций или флаг замены буквенных кодов на готовые символы
     *  true - Форматирование готовыми символами
     *  false - Форматирование буквенными кодами
     */
    public function __construct(array|bool $options = [])
    {
        $this->setOptions($options);

        $this->tokenizer = new Tokenizer();
    }

    /**
     * Задает параметры типографа.
     * @param array|bool $options
     * @return void
     * @see __construct
     */
    public function setOptions(array|bool $options): void
    {
        // Форматирование буквенными кодами
        if ($options === false) {
            $options = [
                'entities' => 'named'
            ];
        }

        // Форматирование готовыми символами
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
            'hyphen-to-nbhy' => true,
        ],
        'nbsp' => [
            'initial' => true,
            'mdash' => true,
            'number' => true,
            'short-word' => true,
        ],
        'special' => [
            'copyright' => true,
            'plus-minus' => true,
            'reg-mark' => true,
            'times' => true,
            'trade' => true,
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
        // Обнуляем метрики
        $this->metrics['tokenizationTime'] = 0.0;
        $this->metrics['processingTime'] = 0.0;
        $this->metrics['totalTime'] = 0.0;
        $this->metrics['memoryUsage'] = 0.0;

        $this->metrics['originalContentLength'] = mb_strlen($text);

        $memory = memory_get_usage();

        $text = trim($text);

        if (!mb_strlen($text)) {
            return $text;
        }

        // Не форматировать сериализованные строки
        if (preg_match('/^[abdisON]:(.*);/', $text)) {
            return $text;
        }

        // Преобразование текста в массив токенов
        $this->tokens = $this->tokenizer->tokenize($text);

        $this->metrics['tokenizationTime'] = $this->tokenizer->getMetrics()['tokenizationTime'];

        // Если токенизация не удалась, вернуть текст без дальнейшей обработки
        if (empty($this->tokenizer->getMetrics()['countOfLines'])) {
            $this->metrics['resultContentLength'] = mb_strlen($text);
            return $text;
        }

        $processingStart = microtime(true);

        // Если обработка кавычек включена
        if (!empty($this->options['quotes'])) {

            // Установка массива кавычек
            Quotes\ReplaceQuotes::setQuoteMarks($this->options['quotes'] ?? []);

            // Сброс счетчиков состояния кавычек
            Quotes\ReplaceQuotes::resetQuoteLevels();
        }

        $tokens_count = count($this->tokens);

        for ($index = 0; $index < $tokens_count; $index++) {

            // Если текущий токен — HTML тег, ничего в нём не трогаем (чтобы не ломать имена/атрибуты)
            $tokenType = $this->tokens[$index]['type'] ?? null;
            if ($tokenType === 'tag') {
                continue;
            }

            // Правила для специальных символов
            if (isset($this->options['special'])) {

                if (!empty($this->options['special']['copyright'])) {
                    // замена (c) → © (&copy;)
                    Special\ReplaceCopyright::apply($index, $this->tokens);
                }

                if (!empty($this->options['special']['plus-minus'])) {
                    // замена +- на ±
                    Special\ReplacePlusMinus::apply($index, $this->tokens);
                }

                if (!empty($this->options['special']['reg-mark'])) {
                    // замена (r) → ® (&reg;)
                    Special\ReplaceRegMark::apply($index, $this->tokens);
                }

                if (!empty($this->options['special']['times'])) {
                    // замена x на × между числами
                    Special\ReplaceTimes::apply($index, $this->tokens);
                }

                if (!empty($this->options['special']['trade'])) {
                    // замена (tm) на ™
                    Special\ReplaceTrademark::apply($index, $this->tokens);
                }
            }

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

            if (!empty($this->options['quotes'])) {

                // Замена апострофа
                Quotes\ReplaceApos::apply($index, $this->tokens);

                // Замена кавычек
                Quotes\ReplaceQuotes::apply($index, $this->tokens);
            }
        }

        // Преобразование символов
        Formatting\HtmlEntities::applyToAll($this->tokens, $this->options['entities']);

        $text = $this->tokenizer->toString($this->tokens);

        $this->metrics['resultContentLength'] = mb_strlen($text);
        $this->metrics['processingTime'] = microtime(true) - $processingStart;
        $this->metrics['totalTime'] = $this->metrics['tokenizationTime'] + $this->metrics['processingTime'];
        $this->metrics['memoryUsage'] = memory_get_usage() - $memory;

        return $text;
    }

    /**
     * Возвращает массив токенов после обработки
     *
     * @param int|null $limit
     * @return array Массив токенов
     */
    public function getTokens(?int $limit = null): array
    {
        if ($limit !== null) {
            return array_slice($this->tokens, 0, $limit);
        }

        return $this->tokens;
    }

    /**
     * Возвращает массив метрик типографа
     *
     * @return array|float[]
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }

}

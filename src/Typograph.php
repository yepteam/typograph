<?php

namespace Yepteam\Typograph;

use Yepteam\Typograph\Helpers\HtmlEntityHelper;
use Yepteam\Typograph\Rules\{BaseRule, Dash, Nbsp, Punctuation, Quotes, Special};

final class Typograph
{
    public const ENTITIES_NAMED = 'named';
    public const ENTITIES_NUMERIC = 'numeric';
    public const ENTITIES_HEX = 'hex';
    public const ENTITIES_RAW = 'raw';

    public const ELLIPSIS_HELLIP = 'hellip';
    public const ELLIPSIS_DOTS = 'dots';

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
     *     quotes?: array<int, array{string, string}>|false|false[]|int[]|null,
     *
     *     // Правила замены знаков минус/дефис/тире
     *     dash?: array{
     *         'hyphen-to-mdash'?: bool, // дефис на mdash
     *         'hyphen-to-minus'?: bool, // дефис на минус
     *         'mdash-to-ndash'?: bool, // mdash на ndash
     *         'ndash-to-mdash'?: bool, // ndash на mdash
     *         'hyphen-to-nbhy'?: bool|int, // неразрывный дефис
     *     }|false|false[]|int[]|null,
     *
     *     // Правила расстановки неразрывных пробелов
     *     nbsp?: array{
     *         'initial'?: bool, // до и после инициалов
     *         'mdash'?: bool, // до и после тире
     *         'number'?: bool, // до и после числа
     *         'short-word'?: bool|int, // до и после короткого слова
     *     }|false|false[]|int[]|null,
     *
     *     // Правила для специальных символов
     *     special?: array{
     *         'copyright'?: bool, // (C) -> © (&copy;)
     *         'plus-minus'?: bool, // замена +- на ± (&plusmn;)
     *         'reg-mark'?: bool,  // (R) -> ® (&reg;)
     *         'times'?: bool, // замена x на × (&times;) — между числами
     *         'trade'?: bool, // замена (tm) на ™
     *     }|false|false[]|null,
     *
     *     // Режим отладки
     *     debug?: bool
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
                'entities' => Typograph::ENTITIES_NAMED
            ];
        }

        // Форматирование готовыми символами
        if ($options === true) {
            $options = [
                'entities' => Typograph::ENTITIES_RAW
            ];
        }

        // Сначала мержим все опции с дефолтными значениями
        $this->options = array_merge($this->defaultOptions, $options);

        // Затем для опциональных массивов применяем особую логику:
        $optionalArrays = ['dash', 'nbsp', 'special'];
        foreach ($optionalArrays as $key) {
            if (array_key_exists($key, $options)) {
                // Проверяем на явное отключение (null, false или пустой массив)
                if ($options[$key] === null || $options[$key] === false || $options[$key] === []) {
                    $this->options[$key] = [];
                } elseif (is_array($options[$key])) {
                    // Мержим с дефолтными значениями
                    $this->options[$key] = array_merge($this->defaultOptions[$key], $options[$key]);
                }
            }
            // Если ключ не указан - дефолтные значения уже установлены в array_merge выше
        }

        // После установки всех опций, инициализируем правила
        $this->initializeRules();
    }

    public function getDefaultOptions(): array
    {
        return $this->defaultOptions;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @var array Массив для хранения параметров типографа
     */
    private array $options = [];

    /**
     * @var array Массив для хранения токенов текста
     */
    private array $tokens = [];

    private array $rules = [];

    /**
     * @var array Параметры по умолчанию
     */
    private array $defaultOptions = [
        'entities' => Typograph::ENTITIES_RAW,
        'ellipsis' => Typograph::ELLIPSIS_HELLIP,
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
        'debug' => false,
    ];

    /**
     * Инициализирует и регистрирует все необходимые правила на основе текущих опций.
     */
    private function initializeRules(): void
    {
        $rules = [];
        $rules = array_merge($rules, $this->getSpecialRules());
        $rules = array_merge($rules, $this->getDashRules());
        $rules = array_merge($rules, $this->getNbspRules());
        $rules = array_merge($rules, $this->getPunctuationRules());
        $rules = array_merge($rules, $this->getQuotesRules());

        $this->rules = array_keys(array_filter($rules));
    }

    /**
     * Правила для специальных символов
     *
     * @return bool[]
     */
    private function getSpecialRules(): array
    {
        return [
            Special\ReplaceCopyright::class => !empty($this->options['special']['copyright']),
            Special\ReplacePlusMinus::class => !empty($this->options['special']['plus-minus']),
            Special\ReplaceRegMark::class => !empty($this->options['special']['reg-mark']),
            Special\ReplaceTimes::class => !empty($this->options['special']['times']),
            Special\ReplaceTrademark::class => !empty($this->options['special']['trade']),
        ];
    }

    /**
     * Правила замены знаков минус/дефис/тире
     *
     * @return bool[]
     */
    private function getDashRules(): array
    {
        return [
            Dash\HyphenToMinus::class => !empty($this->options['dash']['hyphen-to-minus']),
            Dash\HyphenToMdash::class => !empty($this->options['dash']['hyphen-to-mdash']),
            Dash\NdashToMdash::class => !empty($this->options['dash']['ndash-to-mdash']),
            Dash\MdashToNdash::class => !empty($this->options['dash']['mdash-to-ndash']),
            Dash\NonBreakingHyphen::class => !empty($this->options['dash']['hyphen-to-nbhy']),
        ];
    }

    /**
     * Правила расстановки неразрывных пробелов
     *
     * @return bool[]
     */
    private function getNbspRules(): array
    {
        return [
            Nbsp\Number::class => !empty($this->options['nbsp']['number']),
            Nbsp\Mdash::class => !empty($this->options['nbsp']['mdash']),
            Nbsp\Initial::class => !empty($this->options['nbsp']['initial']),
            Nbsp\ShortWord::class => !empty($this->options['nbsp']['short-word']),
        ];
    }

    /**
     * Правила пунктуации
     *
     * @return bool[]
     */
    private function getPunctuationRules(): array
    {
        return [
            Punctuation\ThreeDotsToHellip::class => ($this->options['ellipsis'] ?? null) === Typograph::ELLIPSIS_HELLIP,
            Punctuation\HellipToThreeDots::class => ($this->options['ellipsis'] ?? null) === Typograph::ELLIPSIS_DOTS,
        ];
    }

    /**
     * Правила для кавычек
     *
     * @return bool[]
     */
    private function getQuotesRules(): array
    {
        return [
            Quotes\ReplaceApos::class => !empty($this->options['quotes']),
            Quotes\ReplaceQuotes::class => !empty($this->options['quotes']),
        ];
    }

    /**
     * Форматирует текст с применением типографских правил
     *
     * @param string $text Текст для обработки
     * @return string Обработанный текст с применением типографских правил
     */
    public function format(string $text): string
    {
        // Обнуляем метрики и токены: результаты предыдущего вызова не должны оставаться,
        // если текст будет возвращен без обработки
        $this->tokens = [];
        $this->metrics['originalContentLength'] = mb_strlen($text);
        $this->metrics['resultContentLength'] = $this->metrics['originalContentLength'];
        $this->metrics['tokenizationTime'] = 0.0;
        $this->metrics['processingTime'] = 0.0;
        $this->metrics['totalTime'] = 0.0;
        $this->metrics['memoryUsage'] = 0.0;

        // Не форматировать:
        // - пустой текст;
        // - текст в невалидной кодировке (регулярные выражения с /u на нем не работают);
        // - сериализованные строки
        if ($text === '' || !mb_check_encoding($text, 'UTF-8') || self::isSerialized($text)) {
            return $text;
        }

        $memory = memory_get_usage();

        // Преобразование текста в массив токенов
        $this->tokens = $this->tokenizer->tokenize($text);

        $this->metrics['tokenizationTime'] = $this->tokenizer->getMetrics()['tokenizationTime'];

        $processingStart = microtime(true);

        $tokens_count = count($this->tokens);

        $options = $this->getOptions();

        // Состояние расстановки кавычек: свое для каждого вызова, не разделяется между экземплярами
        $options[Quotes\ReplaceQuotes::STATE_OPTION] = new Quotes\QuotesState();

        for ($index = 0; $index < $tokens_count; $index++) {
            $tokenType = $this->tokens[$index]['type'] ?? null;

            // Пропускаем HTML-теги
            if ($tokenType === 'tag') {
                continue;
            }

            /**
             * @var $ruleClass BaseRule
             */
            foreach ($this->rules as $ruleClass) {
                $ruleClass::apply($index, $this->tokens, $options);
            }
        }

        // Преобразование символов
        HtmlEntityHelper::format($this->tokens, $this->options['entities']);

        $text = $this->tokenizer->toString($this->tokens);

        $this->metrics['resultContentLength'] = mb_strlen($text);
        $this->metrics['processingTime'] = microtime(true) - $processingStart;
        $this->metrics['totalTime'] = $this->metrics['tokenizationTime'] + $this->metrics['processingTime'];
        $this->metrics['memoryUsage'] = memory_get_usage() - $memory;

        return $text;
    }

    /**
     * Грамматика значения в формате serialize().
     * Длины строк не сверяются: их часто указывают в символах вместо байтов.
     */
    private const SERIALIZED_PATTERN = '/^(?<value>
          N;
        | b:[01];
        | i:[+-]?\d+;
        | d:(?:[+-]?(?:\d+(?:\.\d*)?|\.\d+)(?:[eE][+-]?\d+)?|NAN|-?INF);
        | [sS]:\d+:".*?";
        | E:\d+:".*?";
        | [rR]:\d+;
        | a:\d+:\{(?:(?&value)(?&value))*\}
        | O:\d+:"[^"]*":\d+:\{(?:(?&value)(?&value))*\}
        | C:\d+:"[^"]*":\d+:\{.*\}
    )\z/sx';

    /**
     * Является ли текст целиком сериализованным значением
     *
     * @param string $text
     * @return bool
     */
    private static function isSerialized(string $text): bool
    {
        // Сериализованное значение всегда заканчивается на ";" или "}"
        if (!str_ends_with($text, ';') && !str_ends_with($text, '}')) {
            return false;
        }

        return preg_match(self::SERIALIZED_PATTERN, $text) === 1;
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

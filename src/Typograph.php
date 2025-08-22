<?php

namespace Yepteam\Typograph;

use Yepteam\Typograph\Helpers\HtmlEntityHelper;
use Yepteam\Typograph\Rules\{BaseRule, Dash, Nbsp, Punctuation, Quotes, Special};

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

        define('TYPOGRAPH_DEBUG', !empty($this->options['debug'] ?? false));

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
        'debug' => false,
    ];

    /**
     * Инициализирует и регистрирует все необходимые правила на основе текущих опций.
     */
    private function initializeRules(): void
    {
        $this->rules = array_keys(array_filter([
            // Правила для специальных символов
            Special\ReplaceCopyright::class => !empty($this->options['special']['copyright']),
            Special\ReplacePlusMinus::class => !empty($this->options['special']['plus-minus']),
            Special\ReplaceRegMark::class => !empty($this->options['special']['reg-mark']),
            Special\ReplaceTimes::class => !empty($this->options['special']['times']),
            Special\ReplaceTrademark::class => !empty($this->options['special']['trade']),

            // Правила замены знаков минус/дефис/тире
            Dash\HyphenToMinus::class => !empty($this->options['dash']['hyphen-to-minus']),
            Dash\HyphenToMdash::class => !empty($this->options['dash']['hyphen-to-mdash']),
            Dash\NdashToMdash::class => !empty($this->options['dash']['ndash-to-mdash']),
            Dash\MdashToNdash::class => !empty($this->options['dash']['mdash-to-ndash']),
            Dash\NonBreakingHyphen::class => !empty($this->options['dash']['hyphen-to-nbhy']),

            // Правила расстановки неразрывных пробелов
            Nbsp\Number::class => !empty($this->options['nbsp']['number']),
            Nbsp\Mdash::class => !empty($this->options['nbsp']['mdash']),
            Nbsp\Initial::class => !empty($this->options['nbsp']['initial']),
            Nbsp\ShortWord::class => !empty($this->options['nbsp']['short-word']),

            // Правила для многоточий
            Punctuation\ThreeDotsToHellip::class => ($this->options['ellipsis'] ?? null) === 'hellip',
            Punctuation\HellipToThreeDots::class => ($this->options['ellipsis'] ?? null) === 'dots',

            // Правила для кавычек
            Quotes\ReplaceApos::class => !empty($this->options['quotes']),
            Quotes\ReplaceQuotes::class => !empty($this->options['quotes']),
        ]));
    }

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

        $options = $this->getOptions();

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

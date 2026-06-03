PHP-библиотека для типографической обработки текста.

![License](https://img.shields.io/github/license/yepteam/typograph)
![Packagist Version](https://img.shields.io/packagist/v/yepteam/typograph)
![PHP Version](https://img.shields.io/packagist/php-v/yepteam/typograph)
![Packagist Downloads](https://img.shields.io/packagist/dt/yepteam/typograph)

## Возможности

- Замена пробелов и дефисов на неразрывные
- Расстановка короткого и длинного тире
- Замена кавычек первого и второго уровня
- Игнорирование HTML-тегов, Emoji, содержимого тегов script, style и pre
- Кодирование символов в виде мнемоник или числовых кодов

## Требования

- PHP 8.1 или выше
- Расширение `mbstring`

## Установка

Установите пакет через composer:

```bash
composer require yepteam/typograph
```

## Использование

```php
use Yepteam\Typograph\Typograph;

// Инициализация со стандартными параметрами
$typograph = new Typograph();

$html = $typograph->format($html);
```

Конструктор также принимает булев флаг — короткую форму выбора режима кодирования:

```php
new Typograph(true);  // готовыми символами (raw)
new Typograph(false); // буквенными кодами (named)
```

## Конфигурация

В примере ниже указаны параметры по умолчанию.

```php
$typograph = new Typograph([
    // Режим кодирования
    // named - буквенными кодами
    // numeric - числовыми кодами
    // hex - шестнадцатеричными кодами
    // raw - готовыми символами
    'entities' => Typograph::ENTITIES_RAW,
    
    // Многоточия
    // hellip - замена трех точек на символ многоточия
    // dots - замена символа многоточия на три точки
    // none - не обрабатывать многоточия
    'ellipsis' => Typograph::ELLIPSIS_HELLIP,
    
    // Массив кавычек по каждому уровню
    // При пустом массиве обработка кавычек будет отключена
    'quotes' => [
        ['«', '»'], // кавычки 1 уровня
        ['„', '“'], // кавычки 2 уровня
    ],

    // Правила* замены знаков минус/дефис/тире    
    'dash' => [
        'hyphen-to-mdash' => true, // дефис на mdash
        'hyphen-to-minus' => true, // дефис на минус
        'mdash-to-ndash'  => true, // mdash на ndash
        'ndash-to-mdash'  => true, // ndash на mdash
        'hyphen-to-nbhy'  => true, // дефис на неразрывный
    ],
    
    // Правила* расстановки неразрывных пробелов
    'nbsp' => [
        'initial'    => true, // до и после инициалов
        'mdash'      => true, // до и после тире
        'number'     => true, // до и после числа
        'short-word' => true, // до и после короткого слова
    ],
    
    // Правила* обработки специальных символов
    'special' => [
        'copyright'  => true, // (C) на ©
        'plus-minus' => true, // +- на ±
        'reg-mark'   => true, // (R) на ®
        'times'      => true, // x на × между числами
        'trade'      => true, // (rm) на ™
    ],
    
    // Режим отладки правил
    'debug' => false,
]);
```

*Для отключения группы правил укажите пустой массив, false или null.

## Метрики

После вызова `format()` доступны метрики последней обработки:

```php
$typograph->format($html);

$metrics = $typograph->getMetrics();
// [
//     'originalContentLength' => 1234,  // длина исходного текста (символов)
//     'resultContentLength'   => 1256,  // длина результата (символов)
//     'tokenizationTime'      => 0.001, // время токенизации (сек)
//     'processingTime'        => 0.004, // время применения правил (сек)
//     'totalTime'             => 0.005, // суммарное время (сек)
//     'memoryUsage'           => 524288 // использование памяти (байт)
// ]
```

## Дополнительные методы

```php
// Изменить параметры у существующего экземпляра
$typograph->setOptions(['entities' => Typograph::ENTITIES_NAMED]);

// Получить текущие параметры и параметры по умолчанию
$typograph->getOptions();
$typograph->getDefaultOptions();

// Получить массив токенов после обработки (удобно вместе с 'debug' => true)
$typograph->getTokens();
```

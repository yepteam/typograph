PHP-библиотека для типографической обработки текста.

![License](https://img.shields.io/github/license/yepteam/typograph?style=flat-square)
![Packagist Version](https://img.shields.io/packagist/v/yepteam/typograph?style=flat-square)
![Packagist Downloads](https://img.shields.io/packagist/dt/yepteam/typograph?style=flat-square)

## Возможности

- Расстановка неразрывных пробелов и дефисов
- Замена дефисов, короткого и длинного тире
- Замена кавычек первого и второго уровня
- Игнорирование HTML-тегов, содержимого тегов script и style
- Кодирование символов в виде мнемоник или числовых кодов

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

## Пример конфигурации

```php
// Указаны параметры по умолчанию
$typograph = new Typograph([
    // Режим кодирования
    // named - буквенными кодами
    // numeric - числовыми кодами
    // hex - шестнадцатеричными кодами
    // raw - готовыми символами
    'entities' => 'named',
    
    // Многоточия
    // hellip - замена трех точек на символ многоточия
    // dots - замена символа многоточия на три точки
    // none - не обрабатывать многоточия
    'ellipsis' => 'hellip',
    
    // Массив кавычек по каждому уровню
    // При пустом массиве обработка кавычек будет отключена
    'quotes' => [
        ['«', '»'], // кавычки 1 уровня
        ['„', '“'], // кавычки 2 уровня
    ],

    // Правила замены знаков минус/дефис/тире    
    'dash' => [
        'hyphen-to-mdash' => true, // дефис на Mdash
        'hyphen-to-minus' => true, // дефис на минус
        'mdash-to-ndash' => true, // Mdash на Ndash
        'ndash-to-mdash' => true, // Ndash на Mdash
        
        // Замена обычного дефиса на неразрывный
        // указывается максимальная длина строки до/после дефиса
        'hyphen-to-nbhy' => 2, 
    ],
    
    // Правила расстановки неразрывных пробелов
    'nbsp' => [
        'initial' => true, // до и после инициалов
        'mdash' => true, // до и после тире
        'number' => true, // до и после числа
        'short-word' => true, // до и после короткого слова
    ],
]);
```

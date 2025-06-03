PHP-библиотека для типографической обработки текста.

![License](https://img.shields.io/github/license/yepteam/typograph?style=flat-square)
![Packagist Version](https://img.shields.io/packagist/v/yepteam/typograph?style=flat-square)
![Packagist Downloads](https://img.shields.io/packagist/dt/yepteam/typograph?style=flat-square)

## Возможности

- Расстановка неразрывных пробелов
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
    
    // Правила расстановки неразрывных пробелов
    'nbsp' => [
        'initial' => true, // До и после инициалов
        'mdash' => true, // До и после тире
        'number' => true, // До и после числа
        'short-word' => true, // До и после короткого слова
    ],
]);
```

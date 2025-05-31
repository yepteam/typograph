PHP-библиотека для типографической обработки текста.

![License](https://img.shields.io/github/license/yepteam/typograph?style=flat-square)
![Packagist Version](https://img.shields.io/packagist/v/yepteam/typograph?style=flat-square)
![Packagist Downloads](https://img.shields.io/packagist/dt/yepteam/typograph?style=flat-square)

## Установка

Установите пакет через composer:

```bash
composer require yepteam/typograph
```

## Использование

```php
use Yepteam\Typograph\Typograph;

$typograph = new Typograph();

// Форматирование с заменой на готовые символы
$html = $typograph->format($html, true);

// Форматирование с заменой на буквенные коды
$html = $typograph->format($html, false);
```

## Возможности

- Замена дефисов, короткого и длинного тире
- Установка неразрывных пробелов
- Замена кавычек первого и второго уровня
- Игнорирование HTML-тегов, содержимого тегов script и style

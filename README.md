PHP-библиотека для типографической обработки текста.

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
- Замена кавычек

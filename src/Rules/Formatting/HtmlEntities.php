<?php

namespace Yepteam\Typograph\Rules\Formatting;

/**
 * Преобразование специальных символов в HTML-сущности согласно заданному формату
 */
class HtmlEntities
{
    public static array $formats = [
        'named',   // Преобразование в именованные мнемоники
        'numeric', // Десятичными кодами
        'hex',     // Шестнадцатеричными кодами
        'raw',     // Готовыми символами
    ];

    public static function applyToAll(array &$tokens, string $format = 'named'): void
    {
        if (!in_array($format, self::$formats)) {
            $format = 'named';
        }

        if ($format === 'raw') {
            return;
        }

        foreach ($tokens as &$token) {
            if ($token['type'] === 'tag') {
                continue;
            }

            if ($token['type'] === 'entity') {
                continue;
            }

            $token['value'] = self::convert($token['value'], $format);
        }
    }

    public static function decodeEntity(string $encoded): string
    {
        return html_entity_decode($encoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private static function convert(string $text, string $format): string
    {
        $result = '';
        $length = mb_strlen($text, 'UTF-8');

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            $ord = mb_ord($char, 'UTF-8');

            // комбинируемый знак ударения
            if ($ord === 769) {
                $result .= match ($format) {
                    'named', 'numeric' => "&#$ord;",
                    'hex' => "&#x" . dechex($ord) . ";",
                    default => $char
                };
                continue;
            }

            // Неразрывный дефис
            if ($ord === 8209) {
                $result .= match ($format) {
                    'named', 'numeric' => "&#$ord;",
                    'hex' => "&#x" . dechex($ord) . ";",
                    default => $char
                };
                continue;
            }

            // Знак рубля (нет мнемоники в HTML)
            if ($ord === 8381) {
                $result .= match ($format) {
                    'numeric' => "&#$ord;",
                    'hex' => "&#x" . dechex($ord) . ";",
                    default => $char
                };
                continue;
            }

            // Знак номера (появился в HTML5)
            if ($ord === 8470) {
                $result .= match ($format) {
                    'named', 'numeric' => "&#$ord;",
                    'hex' => "&#x" . dechex($ord) . ";",
                    default => $char
                };
                continue;
            }

            // Проверяем, есть ли у символа именованная мнемоника
            $entity = htmlentities($char, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if ($entity === $char) {
                $result .= $char;
                continue;
            }

            // Преобразуем в выбранный формат
            $result .= match ($format) {
                'named' => $entity,
                'numeric' => "&#$ord;",
                'hex' => "&#x" . dechex($ord) . ";",
                default => $char
            };
        }

        return $result;
    }
}

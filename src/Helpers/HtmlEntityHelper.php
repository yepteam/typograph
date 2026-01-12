<?php

namespace Yepteam\Typograph\Helpers;

use Yepteam\Typograph\Typograph;

/**
 * Преобразование специальных символов в HTML-сущности согласно заданному формату
 */
class HtmlEntityHelper
{
    /**
     * @var array|string[]
     */
    public static array $formats = [
        Typograph::ENTITIES_NAMED, // Преобразование в именованные мнемоники
        Typograph::ENTITIES_NUMERIC, // Десятичными кодами
        Typograph::ENTITIES_HEX, // Шестнадцатеричными кодами
        Typograph::ENTITIES_RAW, // Готовыми символами
    ];

    public static function format(array &$tokens, string $format = Typograph::ENTITIES_NAMED): void
    {
        if (!in_array($format, self::$formats)) {
            $format = Typograph::ENTITIES_NAMED;
        }

        if ($format === Typograph::ENTITIES_RAW) {
            return;
        }

        foreach ($tokens as &$token) {
            if ($token['type'] === 'tag') {
                continue;
            }

            if ($token['type'] === 'entity') {
                continue;
            }

            // Проверяем, не содержит ли значение токена HTML-сущности
            if (preg_match('/&(?:[a-zA-Z0-9]+|#[0-9]{1,7}|#x[0-9a-fA-F]{1,6});/', $token['value'])) {
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
                    Typograph::ENTITIES_NAMED, Typograph::ENTITIES_NUMERIC => "&#$ord;",
                    Typograph::ENTITIES_HEX => "&#x" . dechex($ord) . ";",
                    default => $char
                };
                continue;
            }

            // Неразрывный дефис
            if ($ord === 8209) {
                $result .= match ($format) {
                    Typograph::ENTITIES_NAMED, Typograph::ENTITIES_NUMERIC => "&#$ord;",
                    Typograph::ENTITIES_HEX => "&#x" . dechex($ord) . ";",
                    default => $char
                };
                continue;
            }

            // Знак рубля (нет мнемоники в HTML)
            if ($ord === 8381) {
                $result .= match ($format) {
                    Typograph::ENTITIES_NUMERIC => "&#$ord;",
                    Typograph::ENTITIES_HEX => "&#x" . dechex($ord) . ";",
                    default => $char
                };
                continue;
            }

            // Знак номера (появился в HTML5)
            if ($ord === 8470) {
                $result .= match ($format) {
                    Typograph::ENTITIES_NAMED, Typograph::ENTITIES_NUMERIC => "&#$ord;",
                    Typograph::ENTITIES_HEX => "&#x" . dechex($ord) . ";",
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
                Typograph::ENTITIES_NAMED => $entity,
                Typograph::ENTITIES_NUMERIC => "&#$ord;",
                Typograph::ENTITIES_HEX => "&#x" . dechex($ord) . ";",
                default => $char
            };
        }

        return $result;
    }
}

<?php

namespace Yepteam\Typograph\Rules\Special;

/**
 * Заменяет (R) на символ ® в теге <sup>.
 */
class ReplaceRegMark
{
    /**
     * @param int   $index  Индекс текущего токена.
     * @param array &$tokens Массив токенов.
     */
    public static function apply(int $index, array &$tokens): void
    {
        $token = $tokens[$index] ?? null;

        if ($token && $token['type'] === 'reg') {
            // Эта замена возвращает HTML, поэтому мы должны создать токен типа 'tag',
            // чтобы он не кодировался классом HtmlEntities.
            $tokens[$index]['type'] = 'tag';
            $tokens[$index]['name'] = 'sup';
            $tokens[$index]['value'] = '<sup class="reg">&reg;</sup>';
            $tokens[$index]['rule'] = __CLASS__ . ':' . __LINE__;
        }
    }
}
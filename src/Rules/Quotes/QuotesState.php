<?php

namespace Yepteam\Typograph\Rules\Quotes;

/**
 * Состояние расстановки кавычек в рамках одной обработки текста
 */
final class QuotesState
{
    /**
     * @var int Текущий уровень кавычек
     */
    public int $level = -1;

    /**
     * @var bool[] Флаги открытости кавычек на уровнях (для отладки).
     * True - кавычка на уровне с этим индексом открыта
     * False - кавычка на уровне с этим индексом закрыта
     */
    public array $isOpen = [];
}

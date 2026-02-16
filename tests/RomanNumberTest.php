<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class RomanNumberTest extends TestCase
{
    private Typograph $typograph;

    protected function setUp(): void
    {
        $this->typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);
    }

    // ─────────────────────────────────────────────
    // Привязка предлога ПЕРЕД римским числом
    // ─────────────────────────────────────────────

    public function testPrepositionBeforeRoman()
    {
        $this->assertStringContainsString(
            'в&nbsp;XIV веке',
            $this->typograph->format('построен в XIV веке')
        );

        $this->assertStringContainsString(
            'к&nbsp;III тысячелетию',
            $this->typograph->format('относится к III тысячелетию')
        );

        $this->assertStringContainsString(
            'с&nbsp;XX века',
            $this->typograph->format('с XX века по наши дни')
        );
    }

    // ─────────────────────────────────────────────
    // Nbsp ПОСЛЕ римского числа — только в конце предложения
    // ─────────────────────────────────────────────

    public function testRomanBeforeLastWord()
    {
        // Перед точкой — ставим nbsp
        $this->assertStringContainsString(
            'в&nbsp;XIX&nbsp;веке.',
            $this->typograph->format('Фрагмент кирпичной кладки, выложенной еще в XIX веке.')
        );

        $this->assertStringContainsString(
            'в&nbsp;V&nbsp;веке.',
            $this->typograph->format('Он основан в V веке.')
        );

        // Перед запятой — ставим nbsp
        $this->assertStringContainsString(
            'к&nbsp;XI&nbsp;веку, а&nbsp;не&nbsp;к&nbsp;XII',
            $this->typograph->format('относится к XI веку, а не к XII')
        );

        // Конец текста (без точки) — ставим nbsp
        $this->assertStringEndsWith(
            'глава&nbsp;IV',
            $this->typograph->format('см. глава IV')
        );

        $this->assertStringEndsWith(
            'часть&nbsp;V',
            $this->typograph->format('часть V')
        );
    }

    public function testRomanNotBeforeLastWord()
    {
        // Середина предложения — НЕ ставим nbsp после римского числа
        $this->assertStringContainsString(
            'в&nbsp;XIV веке построена',
            $this->typograph->format('построен в XIV веке построена')
        );

        $this->assertStringContainsString(
            'начало XXI века стало',
            $this->typograph->format('начало XXI века стало переломным')
        );
    }

    // ─────────────────────────────────────────────
    // Обе стороны одновременно (конец предложения)
    // ─────────────────────────────────────────────

    public function testBothSidesBinding()
    {
        $this->assertStringContainsString(
            'в&nbsp;XIX&nbsp;веке.',
            $this->typograph->format('выложенной еще в XIX веке.')
        );

        $this->assertStringContainsString(
            'к&nbsp;XI&nbsp;веку.',
            $this->typograph->format('относится к XI веку.')
        );
    }

    // ─────────────────────────────────────────────
    // Перенос строки после слова = конец предложения
    // ─────────────────────────────────────────────

    public function testRomanBeforeNewline()
    {
        $this->assertStringContainsString(
            'в&nbsp;XIV веке' . PHP_EOL,
            $this->typograph->format('построен в XIV веке' . PHP_EOL . 'Позднее перестроен')
        );
    }

    // ─────────────────────────────────────────────
    // Римские числа с тире (диапазоны веков)
    // ─────────────────────────────────────────────

    public function testRomanWithDash()
    {
        $result = $this->typograph->format('искусство XIV-XV веков');
        $this->assertStringContainsString('XV', $result);
        $this->assertStringContainsString('веков', $result);
    }

    // ─────────────────────────────────────────────
    // НЕ должно срабатывать для обычных аббревиатур
    // ─────────────────────────────────────────────

    public function testNotRomanAbbreviations()
    {
        // HTML — последняя L, это римская буква, но в середине предложения
        // nbsp не должен ставиться после HTML
        $result = $this->typograph->format('изучал HTML долго');
        $this->assertStringNotContainsString('HTML&nbsp;долго', $result);

        // PHP — последняя P, не римская буква
        $result = $this->typograph->format('написал на PHP скрипт');
        $this->assertStringNotContainsString('PHP&nbsp;скрипт', $result);
    }

    // ─────────────────────────────────────────────
    // Одиночная римская буква
    // ─────────────────────────────────────────────

    public function testSingleRomanLetter()
    {
        $this->assertStringContainsString(
            'в&nbsp;I&nbsp;веке.',
            $this->typograph->format('в I веке.')
        );
    }

    // ─────────────────────────────────────────────
    // Длинные римские числа
    // ─────────────────────────────────────────────

    public function testLongRomanNumber()
    {
        $this->assertStringContainsString(
            'в&nbsp;MCMLXXXIV',
            $this->typograph->format('в MCMLXXXIV году')
        );
    }

    // ─────────────────────────────────────────────
    // Длинное слово перед римским числом — без nbsp
    // ─────────────────────────────────────────────

    public function testLongWordBeforeRoman()
    {
        $result = $this->typograph->format('столетие XIV было бурным');
        $this->assertStringNotContainsString('столетие&nbsp;XIV', $result);
    }

    // ─────────────────────────────────────────────
    // После запятой
    // ─────────────────────────────────────────────

    public function testRomanAfterComma()
    {
        $result = $this->typograph->format('Людовик XIV, король Франции');
        $this->assertStringContainsString('XIV, король', $result);
    }
}
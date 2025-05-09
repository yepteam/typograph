<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class DashTest extends TestCase
{
    const MDASH = '&mdash;';
    const NDASH = '&ndash;';
    const MINUS = '&minus;';

    public function testDoubleDashToNdash()
    {
        $typograph = new Typograph();

        $original = '--';
        $this->assertEquals(self::MDASH, $typograph->format($original));

        $original = 'Москва -- Сочи';
        $this->assertStringContainsString(self::MDASH, $typograph->format($original));

        $original = '-- Именно так';
        $this->assertStringContainsString(self::MDASH, $typograph->format($original));
    }

    public function testMdashToNdash()
    {
        $typograph = new Typograph();

        $original = '2010&mdash;2020 гг';
        $this->assertStringContainsString(self::NDASH, $typograph->format($original));
    }

    public function testNdashToMdash()
    {
        $typograph = new Typograph();

        $original = '– Что делать?';
        $this->assertStringContainsString(self::MDASH, $typograph->format($original));

        $original = 'слева – справа';
        $this->assertStringContainsString(self::MDASH, $typograph->format($original));
    }

    public function testHyphenToMdash()
    {
        $typograph = new Typograph();

        $original = 'а - аа';
        $this->assertStringContainsString(self::MDASH, $typograph->format($original));

        $original = 'слева** - справа';
        $this->assertStringContainsString(self::MDASH, $typograph->format($original));
    }

    public function testHyphenToMinus()
    {
        $typograph = new Typograph();

        $original = 'Температура: -2°С';
        $this->assertStringContainsString(self::MINUS, $typograph->format($original));
    }

}

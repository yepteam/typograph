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
        $original = '--';
        $this->assertEquals(self::MDASH, Typograph::format($original));

        $original = 'Москва -- Сочи';
        $this->assertStringContainsString(self::MDASH, Typograph::format($original));

        $original = '-- Именно так';
        $this->assertStringContainsString(self::MDASH, Typograph::format($original));
    }

    public function testMdashToNdash()
    {
        $original = '2010&mdash;2020 гг';
        $this->assertStringContainsString(self::NDASH, Typograph::format($original));
    }

    public function testNdashToMdash()
    {
        $original = '– Что делать?';
        $this->assertStringContainsString(self::MDASH, Typograph::format($original));

        $original = 'слева – справа';
        $this->assertStringContainsString(self::MDASH, Typograph::format($original));
    }

    public function testHyphenToMdash()
    {
        $original = 'а - аа';
        $this->assertStringContainsString(self::MDASH, Typograph::format($original));

        $original = 'слева** - справа';
        $this->assertStringContainsString(self::MDASH, Typograph::format($original));
    }

    public function testHyphenToMinus()
    {
        $original = 'Температура: -2°С';
        $this->assertStringContainsString(self::MINUS, Typograph::format($original));
    }

}

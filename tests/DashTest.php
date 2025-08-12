<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class DashTest extends TestCase
{
    public function testDoubleDashToNdash()
    {
        $typograph = new Typograph([
            'entities' => 'raw',
            'nbsp' => [],
        ]);

        $original = '--';
        $expected = '—';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Москва -- Сочи';
        $expected = 'Москва — Сочи';
        $this->assertSame($expected, $typograph->format($original));

        $original = '-- Именно так';
        $expected = '— Именно так';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testMdashToNdash()
    {
        $typograph = new Typograph([
            'entities' => 'named',
            'nbsp' => [],
        ]);

        $original = '2010&mdash;2020 гг';
        $expected = '2010&ndash;2020 гг';
        $this->assertSame($expected, $typograph->format($original));

        $original = '2015—2020 гг';
        $expected = '2015&ndash;2020 гг';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testNdashToMdash()
    {
        $typograph = new Typograph([
            'entities' => 'named',
            'nbsp' => [],
        ]);

        $original = '&ndash; Что делать?';
        $expected = '&mdash; Что делать?';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'туда – сюда';
        $expected = 'туда &mdash; сюда';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testHyphenToMdash()
    {
        $typograph = new Typograph([
            'entities' => 'named',
            'nbsp' => [],
        ]);

        $original = 'туда - сюда';
        $expected = 'туда &mdash; сюда';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'слева** - справа';
        $expected = 'слева** &mdash; справа';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testHyphenToMinus()
    {
        $typograph = new Typograph([
            'entities' => 'named',
            'nbsp' => [],
        ]);

        $original = 'Температура: -2°С';
        $expected = 'Температура: &minus;2&deg;С';
        $this->assertSame($expected, $typograph->format($original));
    }

}

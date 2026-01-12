<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class HtmlEntityTest extends TestCase
{
    public function testPreEntity()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = 'Опера-сказка в&nbsp;3&nbsp;действиях';
        $expected = 'Опера-сказка в&nbsp;3&nbsp;действиях';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testAcute()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
            'nbsp' => [],
        ]);

        $original = 'Тире́ - один из знаков препинания';
        $expected = 'Тире&#769; &mdash; один из знаков препинания';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testGreek()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        /** @noinspection SpellCheckingInspection */
        $original = 'от греч. τύπος';
        $expected = 'от&nbsp;греч. &tau;ύ&pi;&omicron;&sigmaf;';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testQuotes()
    {
        $typographNamed = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
            'nbsp' => [],
        ]);

        $typographNumeric = new Typograph([
            'entities' => Typograph::ENTITIES_NUMERIC,
            'nbsp' => [],
        ]);

        $original = 'ООО «Рога и копыта»';
        $expectedNamed = 'ООО &laquo;Рога и копыта&raquo;';
        $expectedNumeric = 'ООО &#171;Рога и копыта&#187;';
        $this->assertSame($expectedNamed, $typographNamed->format($original));
        $this->assertSame($expectedNumeric, $typographNumeric->format($original));
    }

    public function testRub()
    {
        $typographNamed = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
            'nbsp' => [],
        ]);

        $typographNumeric = new Typograph([
            'entities' => Typograph::ENTITIES_NUMERIC,
            'nbsp' => [],
        ]);

        $original = '100 ₽';
        $expectedNamed = '100 ₽';
        $expectedNumeric = '100 &#8381;';
        $this->assertSame($expectedNamed, $typographNamed->format($original));
        $this->assertSame($expectedNumeric, $typographNumeric->format($original));
    }

    public function testNumero()
    {
        $typographNamed = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
            'nbsp' => [],
        ]);

        $typographNumeric = new Typograph([
            'entities' => Typograph::ENTITIES_NUMERIC,
            'nbsp' => [],
        ]);

        $original = 'Палата № 6';
        $expected = 'Палата &#8470; 6';
        $this->assertSame($expected, $typographNamed->format($original));
        $this->assertSame($expected, $typographNumeric->format($original));
    }

    public function testExistingEntitiesNotDoubleEncoded()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = 'Copyright &copy; 2025';
        $this->assertSame($original, $typograph->format($original));

        $original = '2&times;2';
        $this->assertSame($original, $typograph->format($original));

        $original = '1 &lt; 2 &amp;&amp; 3 &gt; 2';
        $this->assertSame($original, $typograph->format($original));
    }

    public function testCurrencies()
    {
        $typographNamed = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
            'nbsp' => [],
        ]);

        $typographNumeric = new Typograph([
            'entities' => Typograph::ENTITIES_NUMERIC,
            'nbsp' => [],
        ]);

        $original = '$ 100, € 200, ¥ 300';
        $expectedNamed = '$ 100, &euro; 200, &yen; 300';
        $expectedNumeric = '$ 100, &#8364; 200, &#165; 300';

        $this->assertSame($expectedNamed, $typographNamed->format($original));
        $this->assertSame($expectedNumeric, $typographNumeric->format($original));
    }

}

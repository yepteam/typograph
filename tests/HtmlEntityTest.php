<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class HtmlEntityTest extends TestCase
{
    public function testPreEntity()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        $original = 'Опера-сказка в&nbsp;3&nbsp;действиях';
        $expected = 'Опера-сказка в&nbsp;3&nbsp;действиях';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testAcute()
    {
        $typograph = new Typograph([
            'entities' => 'named',
            'nbsp' => [],
        ]);

        $original = 'Тире́ - один из знаков препинания';
        $expected = 'Тире&#769; &mdash; один из знаков препинания';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testGreek()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        /** @noinspection SpellCheckingInspection */
        $original = 'от греч. τύπος';
        $expected = 'от&nbsp;греч. &tau;ύ&pi;&omicron;&sigmaf;';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testQuotes()
    {
        $typographNamed = new Typograph([
            'entities' => 'named',
            'nbsp' => [],
        ]);
        $typographNumeric = new Typograph([
            'entities' => 'numeric',
            'nbsp' => [],
        ]);

        $original = 'ООО «Рога и копыта»';
        $expectedNamed = 'ООО &laquo;Рога и копыта&raquo;';
        $expectedNumeric = 'ООО &#171;Рога и копыта&#187;';
        $this->assertEquals($expectedNamed, $typographNamed->format($original));
        $this->assertEquals($expectedNumeric, $typographNumeric->format($original));
    }

    public function testRub()
    {
        $typographNamed = new Typograph([
            'entities' => 'named',
            'nbsp' => [],
        ]);
        $typographNumeric = new Typograph([
            'entities' => 'numeric',
            'nbsp' => [],
        ]);

        $original = '100 ₽';
        $expectedNamed = '100 ₽';
        $expectedNumeric = '100 &#8381;';
        $this->assertEquals($expectedNamed, $typographNamed->format($original));
        $this->assertEquals($expectedNumeric, $typographNumeric->format($original));
    }

    public function testNumero()
    {
        $typographNamed = new Typograph([
            'entities' => 'named',
            'nbsp' => [],
        ]);
        $typographNumeric = new Typograph([
            'entities' => 'numeric',
            'nbsp' => [],
        ]);

        $original = 'Палата № 6';
        $expected = 'Палата &#8470; 6';
        $this->assertEquals($expected, $typographNamed->format($original));
        $this->assertEquals($expected, $typographNumeric->format($original));
    }


}
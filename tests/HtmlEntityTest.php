<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class HtmlEntityTest extends TestCase
{
    public function testPreEntity()
    {
        $typograph = new Typograph();

        $original = 'Опера-сказка в&nbsp;3&nbsp;действиях';
        $expected = 'Опера-сказка в&nbsp;3&nbsp;действиях';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Опера-сказка в&amp;nbsp;3&amp;nbsp;действиях';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testPreEntityTwice()
    {
        $typograph = new Typograph();

        $original = 'Опера-сказка в&nbsp;3&amp;nbsp;действиях';
        $expected = 'Опера-сказка в&nbsp;3&nbsp;действиях';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Опера-сказка в&amp;nbsp;3&amp;nbsp;действиях';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testGreek()
    {
        $typograph = new Typograph();

        $original = 'от греч. τύπος';
        $expected = 'от&nbsp;греч. &tau;ύ&pi;&omicron;&sigmaf;';
        $this->assertEquals($expected, $typograph->format($original));
    }
}
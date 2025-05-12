<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class HtmlTagTest extends TestCase
{
    public function testTag()
    {
        $typograph = new Typograph();

        $original = '<b>Текст</b>';
        $expected = '<b>Текст</b>';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testTagMultiline()
    {
        $typograph = new Typograph();

        $original = '<button 
    class="btn btn-primary">
    Отправить
</button>';
        $expected = '<button 
    class="btn btn-primary">
Отправить
</button>';
        $this->assertEquals($expected, $typograph->format($original));
    }

}

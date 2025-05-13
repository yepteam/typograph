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
    Отправить   в  корзину
</button>';
        $expected = '<button 
    class="btn btn-primary">
Отправить в&nbsp;корзину
</button>';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testTagScript()
    {
        $typograph = new Typograph();

        $original = '<script>console.log("Hello World!");</script>';
        $expected = '<script>console.log("Hello World!");</script>';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testTagStyle()
    {
        $typograph = new Typograph();

        $original = '<style>body { font-family: "sans-serif" }</style>';
        $expected = '<style>body { font-family: "sans-serif" }</style>';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testTagPre()
    {
        $typograph = new Typograph();

        $original = '<pre>
    Ничего&nbsp;
    не  менять
</pre>';
        $expected = '<pre>
    Ничего&nbsp;
    не  менять
</pre>';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testTagWithAttribute()
    {
        $typograph = new Typograph();

        $original = "<form data-default='{\"br\":\"<br>\"}'>Текст в форме</form>";
        $expected = "<form data-default='{\"br\":\"<br>\"}'>Текст в&nbsp;форме</form>";
        $this->assertEquals($expected, $typograph->format($original));
    }

}

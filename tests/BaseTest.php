<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class BaseTest extends TestCase
{
    public function testEmptyString()
    {
        $typograph = new Typograph(['entities' => 'named']);
        $this->assertSame('', $typograph->format(''));
    }

    public function testEmptySpaces()
    {
        $typograph = new Typograph(['entities' => 'named']);
        $this->assertSame('', $typograph->format(' '));
        $this->assertSame('', $typograph->format('   '));
    }

    public function testExtraSpaces()
    {
        $typograph = new Typograph(['entities' => 'named', 'nbsp' => []]);
        $this->assertSame(
            'В этом тексте лишние пробелы и табуляции',
            $typograph->format('В     этом    тексте  лишние   пробелы  и     табуляции')
        );
    }

    public function testSerializedStr()
    {
        $typograph = new Typograph(['entities' => 'named']);
        $str = 's:11:"Привет, мир";';
        $this->assertSame($str, $typograph->format($str));
    }
}

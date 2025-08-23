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
            $typograph->format("В    этом    тексте  лишние   пробелы  и    \t табуляции")
        );
    }

    public function testLeadingTabs()
    {
        $typograph = new Typograph(['entities' => 'named', 'nbsp' => []]);
        $this->assertSame(
            "\t\t\tТабуляции в начале строки не трогаем",
            $typograph->format("\t\t\tТабуляции в начале строки не\t трогаем")
        );
    }

    public function testSerializedStr()
    {
        $typograph = new Typograph(['entities' => 'named']);
        $str = 's:11:"Привет, мир";';
        $this->assertSame($str, $typograph->format($str));
    }

    public function testDebugTrue()
    {
        $typograph = new Typograph([
            'entities' => 'named',
            'debug' => true
        ]);

        $typograph->format('И так далее');
        $tokens = $typograph->getTokens();

        $nbsp_rules = json_encode($tokens[1]['rules'] ?? '');
        $this->assertStringContainsString("ShortWord", $nbsp_rules);
    }

    public function testDebugFalse()
    {
        $typograph = new Typograph([
            'entities' => 'named',
            'debug' => false
        ]);

        $typograph->format('И так далее');
        $tokens = $typograph->getTokens();

        $nbsp_rules = json_encode($tokens[1]['rules'] ?? '');
        $this->assertStringNotContainsString("ShortWord", $nbsp_rules);
    }

}

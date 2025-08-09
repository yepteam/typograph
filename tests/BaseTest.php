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

    public function testSpaces()
    {
        $typograph = new Typograph(['entities' => 'named']);
        $this->assertSame('', $typograph->format(' '));
        $this->assertSame('', $typograph->format('   '));
    }
}
